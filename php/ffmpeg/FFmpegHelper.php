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
            Logger::verbose("Executing ffprobe: {}", $command);
            exec($command, $out, $ret);
            if ($ret > 0) {
                Logger::error("Failed to execute ffprobe; returned {}", $ret);
                exit($ret);
            }
            self::$probeCache[$inputFile->getFileName()] = $out;
        } else {
            Logger::debug("Found {} in cache", $inputFile->getFileName());
            $out = self::$probeCache[$inputFile->getFileName()];
        }
        return $out;
    }

    public static function isInterlaced($inputFile)
    {
        $command = 'ffmpeg -i "' . $inputFile . '" -vf idet -frames:v 5000 -f rawvideo -y /dev/null 2>&1';
        Logger::info("Checking for interlacing: {}", $command);
        exec($command, $out, $ret);
        Logger::verbose("Output: {}", $out);
        if ($ret > 0) {
            Logger::error("Failed to determine interlacing; returned {}", $ret);
            return false;
        }
        Logger::info("Output: {}", $out);
        $out = implode($out);

        preg_match("/TFF:[ ]+([0-9]+)/", $out, $matches);
        $tff = preg_replace("/[A-Z]+:[ ]+([0-9]+)/", "$1", $matches[0]);
        preg_match("/BFF:[ ]+([0-9]+)/", $out, $matches);
        $bff = preg_replace("/[A-Z]+:[ ]+([0-9]+)/", "$1", $matches[0]);
        Logger::debug("TFF={}", $tff);
        Logger::debug("BFF={}", $bff);
        return ($tff != 0 || $bff != 0);
    }

    public static function execute($listRequests, $outputFile, $exit = TRUE)
    {
        $command = self::generate($listRequests, $outputFile);
        Logger::verbose("Executing ffmpeg: {}", $command);
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
        // $finalCommand .= " " . self::generateVideoArgs($listRequests);
        // $finalCommand .= " " . self::generateAudioArgs($listRequests);
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

    private static function generateVideoArgs($listRequests)
    {
        $fileno = 0;
        $videoTrack = 0;
        foreach ($listRequests as $request) {

            $args = " ";
            foreach ($request->oInputFile->getVideoStreams() as $index => $stream) {
                $args .= " -map " . $fileno . ":" . $index;
                if ("copy" == $request->videoFormat) {
                    $args .= " -c:v:" . $videoTrack . " copy";
                } else if ($request->isHDR()) {
                    $args .= " -c:v:" . $videoTrack . " libx265 -crf 20 -level:v 51 -pix_fmt yuv420p10le -color_primaries 9 -color_trc 16 -colorspace 9 -color_range 1 -profile:v main10";
                } else if ($request->isHwaccel()) {
                    $args .= " -c:v:" . $videoTrack . " hevc_vaapi -qp 20 -level:v 41";
                    if ($request->deinterlace) {
                        $args .= " -vf deinterlace_vaapi=rate=field:auto=1";
                    }
                } else {
                    $args .= " -c:v:" . $videoTrack . " libx265 -crf 20 -level:v 41";
                }
                $args .= " -metadata:s:v:" . $videoTrack . " language=" . $stream->language;
                $videoTrack ++;
            }
            $fileno ++;
        }
        return $args;
    }

    private static function generateArgs($listRequests, FFmpegArgGenerator $generator)
    {
        $fileno = 0;
        $outTrack = 0;
        foreach ($listRequests as $tmpRequest) {
            $args = " ";
	    $streamList = $generator->getStreams($tmpRequest->oInputFile);
	    Logger::debug("Streams: {}", $streamList);
            foreach ($streamList as $index => $stream) {
                $args .= " -map " . $fileno . ":" . $index;
                $args .= " " . $generator->getAdditionalArgs($outTrack ++, $tmpRequest, $stream);
            }
            $fileno ++;
        }
        return $args;
    }

    private static function generateAudioArgs($listRequests)
    {
        $args = " ";
        $fileno = 0;
        $audioTrack = 0;
        foreach ($listRequests as $request) {
            foreach ($request->oInputFile->getAudioStreams() as $index => $stream) {
                $args .= " -map " . $fileno . ":" . $index;
                if ("copy" != $request->audioFormat) {
                    Logger::verbose("Audio Channel Layout Tracks {}", $request->getAudioChannelLayoutTracks());
                    if ($request->audioChannelLayout != NULL && ($request->areAllAudioChannelLayoutTracksConsidered() || in_array($index, $request->getAudioChannelLayoutTracks()))) {
                        Logger::debug("Taking channel layout from request");
                        $channelLayout = $request->audioChannelLayout;
                        if (NULL != $channelLayout && preg_match("/(0-9]+)\.([0-9]+)/", $channelLayout, $matches)) {
                            $channels = $matches[1] + $matches[2];
                        }
                    } else {
                        Logger::debug("Using channel layout from original stream");
                        $channelLayout = $stream->channel_layout;
                        $channels = $stream->channels;
                    }
                    Logger::debug("{} index for file no {} has channelLayout={} and channels={}", $index, $fileno, $channelLayout, $channels);
                    if (NULL != $channelLayout && $channels <= $stream->channels) {
                        // only change the channel layout if the number of original channels is more than requested
                        $channelLayout = preg_replace("/\(.+\)/", '', $channelLayout);
                        $args .= " -filter:a:" . $audioTrack . ' channelmap=channel_layout=' . $channelLayout;
                    }
                    $args .= " -c:a:" . $audioTrack . " " . $request->audioFormat;
                    $args .= " -q:a:" . $audioTrack . " " . $request->audioQuality;
                    Logger::debug("Requsted sample rate vs input sample rate: {} vs {}", $request->audioSampleRate, $stream->audio_sample_rate);
                    $sampleRate = $request->audioSampleRate;
                    if (NULL != $audioSampleRate) {
                        $sampleRate = $stream->audio_sample_rate;
                    }
                    if (NULL != $audioSampleRate) {
                        $args .= " -ar:" . $audioTrack . " " . $audioSampleRate;
                    }
                } else {
                    // specify copy
                    $args .= " -c:a:" . $audioTrack . " copy";
                }
                $args .= " -metadata:s:a:" . $audioTrack . " language=" . $stream->language;
                $audioTrack ++;
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
