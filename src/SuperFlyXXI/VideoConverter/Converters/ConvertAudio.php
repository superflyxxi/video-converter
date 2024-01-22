<?php
namespace SuperFlyXXI\VideoConverter\Converters;

use SuperFlyXXI\VideoConverter\LogWrapper;
use SuperFlyXXI\VideoConverter\Requests\Request;
use SuperFlyXXI\VideoConverter\Normalizers\OneAtATimeNormalizer;
use SuperFlyXXI\VideoConverter\Normalizers\Normalizer;

class ConvertAudio
{
    public static LogWrapper $log;

    public static Normalizer $normalizer;
   
    public static function convert($oRequest)
    {
        $arrAdditionalRequests = [];
        if ("copy" != $oRequest->audioFormat && count($oRequest->normalizeAudioTracks)) {
            foreach ($oRequest->normalizeAudioTracks as $index) {
                $arrAdditinalRequests[] = self::$normalizer->normalize($oRequest, $index);
            }
        }
        return $arrAdditionalRequests;
    }
}

ConvertAudio::$log = new LogWrapper("ConvertAudio");
ConvertAudio::$normalizer = new OneAtATimeNormalizer();
