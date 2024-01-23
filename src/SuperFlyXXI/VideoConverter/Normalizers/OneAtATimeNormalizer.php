<?php
namespace SuperFlyXXI\VideoConverter\Normalizers;

use SuperFlyXXI\VideoConverter\LogWrapper;
use SuperFlyXXI\VideoConverter\Exceptions\ExecutionException;
use SuperFlyXXI\VideoConverter\Requests\Request;
use SuperFlyXXI\VideoConverter\Helpers\EnvHelper;
use SuperFlyXXI\VideoConverter\Normalizers\Normalizer;
use SuperFlyXXI\VideoConverter\Normalizers\VolumeAnalyzer;

class OneAtATimeNormalizer implements Normalizer
{
    public static LogWrapper $log;

    private VolumeAnalyzer $volAnalyzer;
   
    public function __construct()
    {
        $this->volAnalyzer = new VolumeAnalyzer();
    }

    public function normalize(Request $oRequest, int $index): Request
    {
        $normFile = EnvHelper::getEnvWithDefault("TMP_DIR", "/tmp") . PATH_SEPARATOR .
            $oRequest->oInputFile->getTemporaryFileNamePrefix() . $index . "-norm.mkv";
        $command = 'ffmpeg -i "' . $oRequest->oInputFile->getFileName() . '" -y ';

        $command .= $this->appendNormalizedArgs($oRequest, $index, $index);

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
        $oNewRequest->setAudioTracks($index);
        $oNewRequest->setVideoTracks(null);
        $oNewRequest->setSubtitleTracks(null);
        $oNewRequest->audioFormat = "copy";
        $stream = $oRequest->oInputFile->getAudioStreams()[$index];
        $oNewRequest->audioTitle = "Normalized " . $stream->title;
        return $oNewRequest;
    }

    private function appendNormalizedArgs(Request $oRequest, int $index, int $outindex): string
    {
        $stream = $oRequest->oInputFile->getAudioStreams()[$index];
        $json = $this->volAnalyzer->analyzeAudio($oRequest->oInputFile->getFileName(), $index);

        $normChannelMap = $this->getNormalizedChannelMap($oRequest, $index, $stream);

        $command = ' -map 0:' . $index;
        $command .= ' -filter:a:' . $outindex . ' "';
        $command .= 'loudnorm=measured_I=' . $json["input_i"] . ":measured_TP=" .
                $json["input_tp"] . ":measured_LRA=" . $json["input_lra"] . ":measured_thresh=" . $json["input_thresh"];
        if (null != $normChannelMap) {
            $command .= ',channelmap=channel_layout=' . $normChannelMap;
        }
        $command .= '"  -metadata:s:a:' . $outindex . ' "title=Normalized ' . $stream->language . " " . $normChannelMap
                . '"';
        return $command;
    }

    /**
     *
     * @param
     *            oRequest
     * @param
     *            index
     */
    private function getNormalizedChannelMap($oRequest, $index, $stream)
    {
        $normChannelMap = $oRequest->areAllAudioChannelLayoutTracksConsidered() ||
            in_array($index, $oRequest->getAudioChannelLayoutTracks())
                ? $oRequest->audioChannelLayout
                : $stream->channel_layout;
        if (null == $normChannelMap) {
            $normChannelMap = $stream->channel_layout;
        }
        $normChannelMap = preg_replace("/\(.+\)/", "", $normChannelMap);
        return $normChannelMap;
    }
}

OneAtATimeNormalizer::$log = new LogWrapper("OneAtATimeNormalizer");
