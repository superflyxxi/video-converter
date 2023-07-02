<?php
namespace SuperFlyXXI\VideoConverter\Converters;

use SuperFlyXXI\VideoConverter\LogWrapper;
use SuperFlyXXI\VideoConverter\Exceptions\ExecutionException;
use SuperFlyXXI\VideoConverter\Requests\Request;
use SuperFlyXXI\VideoConverter\Helpers\EnvHelper;

class ConvertAudio
{
    public static $log;

    public static function convert($oRequest)
    {
        $arrAdditionalRequests = [];
        if ("copy" != $oRequest->audioFormat && count($oRequest->normalizeAudioTracks)) {
            // only do this there are tracks to normalize
            $arrAdditionalRequests = self::generateNewRequestsForTracks($oRequest);
        }
        return $arrAdditionalRequests;
    }

    private static function generateNewRequestsForTracks(Request $oRequest): array
    {
        $arrNewRequests = [];
        foreach ($oRequest->normalizeAudioTracks as $index) {
            $arrNewRequests[] = self::generateNewRequest($oRequest, $index);
        }

        return $arrNewRequests;
    }
    private static function generateNewRequest($oRequest, $index): Request
    {
        $stream = $oRequest->oInputFile->getAudioStreams()[$index];
        $json = self::analyzeAudio($oRequest->oInputFile->getFileName(), $index);

        $request = new Request($oRequest->oInputFile->getFileName());
        $request->setAudioTracks($index);
        $request->audioFormat = $oRequest->audioFormat;
        $request->audioQuality = $oRequest->audioQuality;
        if ($oRequest->areAllAudioChannelLayoutTracksConsidered()
                    || in_array($index, $oRequest->getAudioChannelLayoutTracks())) {
            $request->setAudioChannelLayoutTracks($index);
            $request->audioChannelLayout = $oRequest->audioChannelLayout;
        }
        $request->setVideoTracks(null);
        $request->setSubtitleTracks(null);
        $request->setNormalizeAudioTracks(null);
        $request->customFilter = 'loudnorm=measured_I=' . $json["input_i"] . ":measured_TP=" . $json["input_tp"] .
            ":measured_LRA=" . $json["input_lra"] . ":measured_thresh=" . $json["input_thresh"];
        $request->audioTitle = "Normalized " . $stream->title;
        $request->prepareStreams();

        self::$log->debug("Additional request created", [
            "request" => $request,
            "index" => $index
        ]);

        return $request;
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
