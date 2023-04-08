<?php
namespace SuperFlyXXI\VideoConverter\Converters;

use SuperFlyXXI\VideoConverter\LogWrapper;

class ConvertVideo
{
    public static $log;

    public static function convert($oRequest): array
    {
        return [];
    }
}

ConvertVideo::$log = new LogWrapper("ConvertVideo");
