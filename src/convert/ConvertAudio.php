<?php
require_once "LogWrapper.php";
require_once "functions.php";
require_once "request/Request.php";
require_once "Stream.php";
require_once "exceptions/ExecutionException.php";

class ConvertAudio
{
    public static $log;

    public static function convert($oRequest)
    {
        $arrAdditionalRequests = [];
        if ("copy" != $oRequest->audioFormat && count($oRequest->normalizeAudioTracks)) {
            // only do this there are tracks to normalize
            $dir = getEnvWithDefault("TMP_DIR", "/tmp") . PATH_SEPARATOR;
            // any track that is not needed, just copy it to its own file
            foreach ($oRequest->normalizeAudioTracks as $index => $stream) {
                $arrAdditionalRequests[] = self::normalize(
                    $oRequest,
                    $index,
                    $dir,
                    $oRequest->oInputFile->getFileName(),
                    $stream
                );
            }
        }
        return $arrAdditionalRequests;
    }

    private static function normalize($oRequest, $index, $dir, $inFileName, $stream)
    {
        // if the track is to be normalized, now let's normalize it and put it in
        self::$log->info("Normalizing track", [
            "filename" => $oRequest->oInputFile->getFileName(),
            "index" => $index
        ]);
        $json = self::analyzeAudio($inFileName, $index);

        $normFile = $dir . $oRequest->oInputFile->getTemporaryFileNamePrefix() . $index . "-norm.mkv";
        $normChannelMap = $oRequest->areAllAudioChannelLayoutTracksConsidered() ||
            in_array($index, $oRequest->getAudioChannelLayoutTracks()) ? $oRequest->audioChannelLayout : $stream->channel_layout;
        if (null == $normChannelMap) {
            $normChannelMap = $stream->channel_layout;
        }
        $normChannelMap = preg_replace("/\(.+\)/", "", $normChannelMap);

        $sampleRate = $oRequest->audioSampleRate;
        if (null == $sampleRate) {
            $sampleRate = $stream->audio_sample_rate;
        }

        $command = 'ffmpeg -i "' . $inFileName . '" -y -map 0:' . $index;
        $command .= ' -filter:a "loudnorm=measured_I=' . $json["input_i"] . ":measured_TP=" . $json["input_tp"] .
            ":measured_LRA=" . $json["input_lra"] . ":measured_thresh=" . $json["input_thresh"];
        if (null != $normChannelMap) {
            $command .= ",channelmap=channel_layout=" . $normChannelMap;
        }
        $command .= '" ';
        $command .= " -c:a " . $oRequest->audioFormat;
        $command .= " -q:a " . $oRequest->audioQuality;
        if (null != $sampleRate) {
            $command .= " -ar " . $sampleRate;
        }
        $command .= ' -metadata:s:a:0 "title=Normalized ' . $stream->language . " " . $normChannelMap . '"';
        $command .= ' -f matroska "' . $normFile . '" 2>&1';

        self::$log->debug(
            "Normalizing track with command",
            [
                "filename" => $oRequest->oInputFile->getFileName(),
                "index" => $index
            ]
        );
        self::$log->notice("Executing command", [
            "command" => $command
        ]);
        passthru($command, $return);
        if ($return != 0) {
            throw new ExecutionException("ffmpeg", $return, $command);
        }
        $oNewRequest = new Request($normFile);
        $oNewRequest->setAudioTracks("0");
        $oNewRequest->setVideoTracks(null);
        $oNewRequest->setSubtitleTracks(null);
        $oNewRequest->audioFormat = "copy";
        return $oNewRequest;
    }

    /**
     *
     * @param
     *            inFileName The filename to analyze
     */
    private static function analyzeAudio($inFileName, $index)
    {
        $command = 'ffmpeg -hide_banner -i "' . $inFileName . '" -map 0:' . $index .
            ' -filter:a loudnorm=print_format=json -f null - 2>&1';
        self::$log->debug("Analyzing audio for normalization", [
            "filename" => $inFileName,
            "index" => $index
        ]);
        self::$log->notice("Executing command", [
            "command" => $command
        ]);
        exec($command, $out, $return);
        if ($return != 0) {
            throw new ExecutionException("ffmpeg", $return, $command);
        }
        self::$log->debug("Command output", [
            "output" => $out
        ]);
        $out = implode(array_slice($out, - 12));
        $json = json_decode($out, true);
        return $json;
    }
}

ConvertAudio::$log = new LogWrapper("ConvertAudio");
