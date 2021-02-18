<?php
include_once "Request.php";
include_once "functions.php";
include_once "OutputFile.php";
include_once "Logger.php";
include_once "ffmpeg/generators/FFmpegArgGenerator.php";
include_once "ffmpeg/generators/FFmpegVideoArgGenerator.php";
include_once "ffmpeg/generators/FFmpegAudioArgGenerator.php";
include_once "ffmpeg/generators/FFmpegSubtitleArgGenerator.php";
include_once "exceptions/ExecutionException.php";

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
            Logger::verbose("Adding to cache {} = {}", $inputFile->getFileName(), $out);
            self::$probeCache[$inputFile->getFileName()] = $out;
        } else {
            Logger::debug("Found {} in cache", $inputFile->getFileName());
            $out = self::$probeCache[$inputFile->getFileName()];
        }
        return $out;
    }

    public static function isInterlaced($inputFile)
    {
        Logger::info("Checking for interlacing: {}", $inputFile);
        $args = '-i "' . $inputFile . '" -ss 00:05:00 -to 00:10:00 -vf idet -f rawvideo -y /dev/null 2>&1';
        $command = 'ffmpeg ' . $args;
        Logger::debug("Command: {}", $command);
        exec($command, $out, $ret);
        if ($ret > 0) {
            throw new ExecutionException("ffmpeg", $ret, $args);
        }
        Logger::verbose("Output: {}", $out);
        $out = implode($out);

        preg_match("/Progressive:[ ]+([0-9]+)/", $out, $matches);
        $progressive = preg_replace("/[A-Z]+:[ ]+([0-9]+)/", "$1", $matches[0]);
        preg_match("/TFF:[ ]+([0-9]+)/", $out, $matches);
        $tff = preg_replace("/[A-Z]+:[ ]+([0-9]+)/", "$1", $matches[0]);
        preg_match("/BFF:[ ]+([0-9]+)/", $out, $matches);
        $bff = preg_replace("/[A-Z]+:[ ]+([0-9]+)/", "$1", $matches[0]);
	$total = $progressive + $tff + $bff;
        Logger::debug("TFF={}; BFF={}", $tff, $bff);
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
