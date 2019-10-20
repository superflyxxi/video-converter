<?php
include_once "Request.php";
include_once "functions.php";
include_once "OutputFile.php";
include_once "Logger.php";
include_once "ffmpeg/generators/FFmpegArgGenerator.php";
include_once "ffmpeg/generators/FFmpegVideoArgGenerator.php";
include_once "ffmpeg/generators/FFmpegAudioArgGenerator.php";

class FFmpegHelper
{

    private static $probeCache = array();

    public static function probe($inputFile)
    {
        if (! in_array($inputFile->getFileName(), self::$probeCache)) {
            $command = 'ffprobe -v quiet -print_format json -show_format -show_streams "' . $inputFile->getPrefix() . $inputFile->getFileName() . '"';
            Logger::debug("Executing ffprobe: {}", $command);
            exec($command, $out, $ret);
            if ($ret > 0) {
                Logger::error("Failed to execute ffprobe; returned {}", $ret);
                exit($ret);
            }
            Logger::verbose("Adding to cache {}={}", $inputFile->getFileName(), $out);
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
        $command = 'ffmpeg -i "' . $inputFile . '" -vf idet -frames:v 5000 -f rawvideo -y /dev/null 2>&1';
        Logger::debug("Command: {}", $command);
        exec($command, $out, $ret);
        Logger::verbose("Output: {}", $out);
        if ($ret > 0) {
            Logger::error("Failed to determine interlacing; returned {}", $ret);
            return false;
        }
        $out = implode($out);

        preg_match("/TFF:[ ]+([0-9]+)/", $out, $matches);
        $tff = preg_replace("/[A-Z]+:[ ]+([0-9]+)/", "$1", $matches[0]);
        preg_match("/BFF:[ ]+([0-9]+)/", $out, $matches);
        $bff = preg_replace("/[A-Z]+:[ ]+([0-9]+)/", "$1", $matches[0]);
        Logger::debug("TFF={}; BFF={}", $tff, $bff);
        return ($tff != 0 || $bff != 0);
    }

    public static function execute($listRequests, $outputFile, $exit = TRUE)
    {
        $command = self::generate($listRequests, $outputFile);
        Logger::debug("Executing ffmpeg: {}", $command);
        passthru($command . " 2>&1", $ret);
        if ($exit && $ret > 0) {
            Logger::error("Failed to execute ffmpeg with return code {}", $ret);
            exit($ret);
        }
        return $ret;
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
        $finalCommand .= " " . self::generateSubtitleArgs($listRequests);

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

    private static function generateSubtitleArgs($listRequests)
    {
        $args = " ";
        $fileno = 0;
        $subtitleTrack = 0;
        foreach ($listRequests as $request) {
            foreach ($request->oInputFile->getSubtitleStreams() as $index => $stream) {
                $args .= " -map " . $fileno . ":" . $index . " -c:s:" . $subtitleTrack . " " . $request->subtitleFormat;
                $args .= " -metadata:s:s:" . $subtitleTrack . " language=" . $stream->language;
                $subtitleTrack ++;
            }
            $fileno ++;
        }
        return $args;
    }
}

?>
