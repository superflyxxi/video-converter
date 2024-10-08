<?php
namespace SuperFlyXXI\VideoConverter\Converters;

use SuperFlyXXI\VideoConverter\LogWrapper;
use SuperFlyXXI\VideoConverter\Requests\Request;
use SuperFlyXXI\VideoConverter\Normalizers\OneAtATimeNormalizer;
use SuperFlyXXI\VideoConverter\Normalizers\AllAtOnceNormalizer;
use SuperFlyXXI\VideoConverter\Normalizers\Normalizer;

class ConvertAudio
{
    public static LogWrapper $log;

    public static Normalizer $normalizer;

    public static function convert($oRequest)
    {
        $arrAdditionalRequests = [];
        if (count($oRequest->normalizeAudioTracks)) {
            foreach ($oRequest->normalizeAudioTracks as $index) {
                $arrAdditionalRequests[] = self::$normalizer->normalize($oRequest, $index);
            }
        }
        return $arrAdditionalRequests;
    }
}

ConvertAudio::$log = new LogWrapper("ConvertAudio");
ConvertAudio::$normalizer = new OneAtATimeNormalizer();
