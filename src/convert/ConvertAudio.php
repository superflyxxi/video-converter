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

    private static function normalize(Request $oRequest): void
    {
        $normFile = getEnvWithDefault("TMP_DIR", "/tmp") . PATH_SEPARATOR .
            $oRequest->oInputFile->getTemporaryFileNamePrefix() . "-norm.mkv";
        $command = 'ffmpeg -i "' . $oRequest->oInputFile->getFileName() . '" -y ';

        $outindex = 0;
        $outaudiotracks = "";
        foreach ($oRequest->normalizeAudioTracks as $index => $stream) {
            self::appendNormalizedArgs($oRequest->oInputFile->getFileName(), $stream, $command, $outindex);
            $outaudiotracks .= $outindex . ' ';
            $outindex ++;
        }

        $command .= " -c:a " . $oRequest->audioFormat;
        $command .= " -q:a " . $oRequest->audioQuality;
        if (null != $oRequest->audioSampleRate) {
            $command .= " -ar " . $oRequest->audioSampleRate;
        }
        $command .= ' -f matroska "' . $normFile . '" 2>&1';

        self::$log->info("Normalizing tracks with command", [
            "filename" => $oRequest->oInputFile->getFileName()
        ]);
        self::$log->notice("Executing command", [
            "command" => $command
        ]);
        passthru($command, $return);
        if ($return != 0) {
            throw new ExecutionException("ffmpeg", $return, $command);
        }
        $oNewRequest = new Request($normFile);
        $oNewRequest->setAudioTracks(trim($outaudiotracks));
        $oNewRequest->setVideoTracks(null);
        $oNewRequest->setSubtitleTracks(null);
        $oNewRequest->audioFormat = "copy";
        return $oNewRequest;
    }

    private static function appendNormalizedArgs(Request $oRequest, Stream $instream, string $command, int $outindex): string
    {
        $json = self::analyzeAudio($oRequest->oInputFile->getFileName(), $instream->index);

        $normChannelMap = $oRequest->areAllAudioChannelLayoutTracksConsidered() ||
            in_array($instream->index, $oRequest->getAudioChannelLayoutTracks()) ? $oRequest->audioChannelLayout : $instream->channel_layout;
        if (null == $normChannelMap) {
            $normChannelMap = $instream->channel_layout;
        }
        $normChannelMap = preg_replace("/\(.+\)/", "", $normChannelMap);

        $command .= ' -map 0:' . $instream->index;
        $command .= ' -filter:a "loudnorm=measured_I=' . $json["input_i"] . ":measured_TP=" . $json["input_tp"] .
            ":measured_LRA=" . $json["input_lra"] . ":measured_thresh=" . $json["input_thresh"];
        if (null != $normChannelMap) {
            $command .= ',channelmap=channel_layout=' . $normChannelMap;
        }
        $command .= '"  -metadata:s:a:' . $outindex . ' "title=Normalized ' . $instream->language . " " . $normChannelMap .
            '"';
        return $command;
    }

    /**
     *
     * @param
     *            inFileName The filename to analyze
     */
    private static function analyzeAudio(string $inFileName, int $index): array
    {
        self::$log->info("Analyzing audio track", [
            "filename" => $inFileName,
            "index" => $index
        ]);
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
