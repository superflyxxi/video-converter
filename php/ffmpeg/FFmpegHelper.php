<?php
require_once "request/Request.php";
require_once "functions.php";
require_once "OutputFile.php";
require_once "Logger.php";
require_once "ffmpeg/generators/FFmpegArgGenerator.php";
require_once "ffmpeg/generators/FFmpegVideoArgGenerator.php";
require_once "ffmpeg/generators/FFmpegAudioArgGenerator.php";
require_once "ffmpeg/generators/FFmpegSubtitleArgGenerator.php";
require_once "exceptions/ExecutionException.php";

class FFmpegHelper
{

    private static $probeCache = array();

    public static function probe($inputFile)
    {
        if (! array_key_exists($inputFile->getFileName(), self::$probeCache)) {
            $command = 'ffprobe -v quiet -print_format json -show_format -show_streams "' . $inputFile->getPrefix() . $inputFile->getFileName() . '"';
            Logger::debug("Executing ffprobe: {}", $command);
            exec($command, $out, $ret);
            if ($ret > 0) {
                throw new ExecutionException("ffprobe", $ret);
            }
            $json = json_decode(implode($out), true);
            Logger::verbose("Adding to probe cache {} = {}", $inputFile->getFileName(), $json);
            self::$probeCache[$inputFile->getFileName()] = $json;
        } else {
            Logger::debug("Found {} in probe cache", $inputFile->getFileName());
            $json = self::$probeCache[$inputFile->getFileName()];
        }
        if (false == $json) {
            return false;
        }
        return $json; //json_decode(implode($out), true);
    }

    public static function isInterlaced($inputFile)
    {
        switch (getEnvWithDefault("DEINTERLACE_CHECK", "probe")) {
            case "idet": 
                return self::isInterlacedBasedOnIdet($inputFile);
                break;
            case "probe":
                return self::isInterlacedBasedOnProbe($inputFile);
                break;
        }
        return false;
    }

    private static function isInterlacedBasedOnProbe($inputFile) {
        $json = self::probe($inputFile);
        print_r($json);
        $stream = $json["streams"][0];
        return array_key_exists("field_order", $stream) && $stream["field_order"] != "progressive";
    }

    private static function isInterlacedBasedOnIdet($inputFile)
    {
        Logger::info("Checking for interlacing: {}", $inputFile->getFileName());
        $args = '-i "' . $inputFile->getFileName() . '" -ss 00:05:00 -to 00:10:00 -vf idet -f rawvideo -y /dev/null 2>&1';
        $command = 'ffmpeg ' . $args;
        Logger::debug("Command: {}", $command);
        exec($command, $out, $ret);
        if ($ret > 0) {
            throw new ExecutionException("ffmpeg", $ret, $args);
        }
        Logger::verbose("Output: {}", $out);
        $out = implode($out);
	/*
[Parsed_idet_0 @ 0x559b53b4b700] Repeated Fields: Neither: 14385 Top:     1 Bottom:     2
[Parsed_idet_0 @ 0x559b53b4b700] Single frame detection: TFF:    10 BFF:    13 Progressive:  8535 Undetermined:  5830
[Parsed_idet_0 @ 0x559b53b4b700] Multi frame detection: TFF:     0 BFF:     0 Progressive: 14365 Undetermined:    23
	*/
        preg_match("/Progressive:[ ]+([0-9]+)/", $out, $matches);
        $progressive = preg_replace("/[A-Za-z]+:[ ]+([0-9]+)/", "$1", $matches[0]);
        preg_match("/TFF:[ ]+([0-9]+)/", $out, $matches);
        $tff = preg_replace("/[A-Za-z]+:[ ]+([0-9]+)/", "$1", $matches[0]);
        preg_match("/BFF:[ ]+([0-9]+)/", $out, $matches);
        $bff = preg_replace("/[A-Za-z]+:[ ]+([0-9]+)/", "$1", $matches[0]);
	$total = $progressive + $tff + $bff;
        Logger::debug("Progressive={}; TFF={}; BFF={}; Total={}", $progressive, $tff, $bff, $total);
	// if percentage of frames are > 1% interlaced, then de-interlace
        return ($tff/$total > 0.01 || $bff/$total > 0.01);
    }

    public static function execute($listRequests, $outputFile, $exit = TRUE)
    {
        $command = self::generate($listRequests, $outputFile);
        Logger::debug("Executing ffmpeg: {}", $command);
        passthru($command . " 2>&1", $ret);
        if ($ret > 0) {
            throw new ExecutionException("ffmpeg", $ret, $command);
        }
    }

    public static function generate($listRequests, $outputFile)
    {
        $finalCommand = "ffmpeg ";
        if (getEnvWithDefault("OVERWRITE_FILE", "true") == "true") {
            $finalCommand .= "-y ";
        }
        $finalCommand .= self::generateHardwareAccelArgs();

        // generate input args
        foreach ($listRequests as $tmpRequest) {
            $finalCommand .= ' -i "' . $tmpRequest->oInputFile->getPrefix() . $tmpRequest->oInputFile->getFileName() . '" ';
        }

        Logger::info("Generating video args");
        $finalCommand .= " " . self::generateArgs($listRequests, new FFmpegVideoArgGenerator());
        Logger::info("Generating audio args");
        $finalCommand .= " " . self::generateArgs($listRequests, new FFmpegAudioArgGenerator());
        Logger::info("Generating subtitle args");
        $finalCommand .= " " . self::generateArgs($listRequests, new FFmpegSubtitleArgGenerator());

        $finalCommand .= self::generateGlobalMetadataArgs($outputFile);
        if ($outputFile->format != NULL) {
            $finalCommand .= ' -f ' . $outputFile->format;
        }
        $finalCommand .= ' "' . $outputFile->getFileName() . '"';

        return $finalCommand;
    }

    private static function generateHardwareAccelArgs()
    {
        return " " . (file_exists("/dev/dri") ? "-hwaccel vaapi -hwaccel_output_format vaapi -hwaccel_device /dev/dri/renderD128" : " ");
    }

    private static function generateGlobalMetadataArgs($outputFile)
    {
        return " " . (NULL != $outputFile->title ? '-metadata "title=' . $outputFile->title . '"' : " ") . " " . (NULL != $outputFile->subtitle ? '-metadata "subtitle=' . $outputFile->subtitle . '"' : " ") . " " . (NULL != $outputFile->year ? '-metadata "year=' . $outputFile->year . '"' : " ") . " " . (NULL != $outputFile->season ? '-metadata "season=' . $outputFile->season . '"' : " ") . " " . (NULL != $outputFile->episode ? '-metadata "episode=' . $outputFile->episode . '"' : " ") . " " . getEnvWithDefault("OTHER_METADATA", " ");
    }

    private static function generateArgs($listRequests, FFmpegArgGenerator $generator)
    {
        $fileno = 0;
        $outTrack = 0;
        $args = " ";
        foreach ($listRequests as $tmpRequest) {
            $streamList = $generator->getStreams($tmpRequest->oInputFile);
            Logger::verbose("File {}, Streams: {}", $tmpRequest->oInputFile->getFileName(), $streamList);
            foreach ($streamList as $index => $stream) {
                $args .= " -map " . $fileno . ":" . $index;
                $args .= " " . $generator->getAdditionalArgs($outTrack ++, $tmpRequest, $index, $stream);
            }
            $fileno ++;
        }
        return $args;
    }
}

?>
