<?php
namespace SuperFlyXXI\VideoConverter\Normalizers;

use SuperFlyXXI\VideoConverter\LogWrapper;
use SuperFlyXXI\VideoConverter\Exceptions\ExecutionException;
use SuperFlyXXI\VideoConverter\Helpers\EnvHelper;
use SuperFlyXXI\VideoConverter\Requests\Request;
use SuperFlyXXI\VideoConverter\Normalizers\Normalizer;
use SuperFlyXXI\VideoConverter\Normalizers\VolumeAnalyzer;

class AllAtOnceNormalizer implements Normalizer
{
    public static LogWrapper $log;

    private VolumeAnalyzer $volAnalyzer;
   
    public function __construct()
    {
        $this->volAnalyzer = new VolumeAnalyzer();
    }

    public function normalize(Request $oRequest, int $index): Request
    {
        $stream = $oRequest->oInputFile->getAudioStreams()[$index];
        $json = $this->volAnalyzer->analyzeAudio($oRequest->oInputFile->getFileName(), $index);

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

AllAtOnceNormalizer::$log = new LogWrapper("AllAtOnceNormalizer");
