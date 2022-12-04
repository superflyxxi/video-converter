<?php

class Options
{
    private static $opts;
    private static $inputfile;

    public static function init($args = null)
    {
        global $argv;
        if (null === $args) {
            $otherArgs = null;
            self::$opts = getopt(
                "",
                [
                    "log-level::",
                    "title:",
                    "year::",
                    "season::",
                    "episode::",
                    "show-title::",
                    "video-tracks::",
                    "video-format::",
                    "video-upscale::",
                    "deinterlace::",
                    "deinterlace-check::",
                    "audio-tracks::",
                    "audio-format::",
                    "audio-quality::",
                    "audio-sample-rate::",
                    "audio-channel-layout::",
                    "audio-channel-layout-tracks::",
                    "normalize-audio-tracks::",
                    "subtitle-tracks::",
                    "subtitle-format::",
                    "subtitle-conversion-output::",
                    "subtitle-conversion-blacklist::",
                    "hdr",
                    "disable-postfix",
                    "playlist::",
                ],
                $otherArgs
            );
            $otherArgs = array_slice($argv, $otherArgs);
            self::$inputfile = empty($otherArgs) ? null : $otherArgs[0];
        } else {
            self::$opts = $args;
            self::$inputfile = $args["inputfile"];
        }
    }

    public static function getInputFile()
    {
        return self::$inputfile;
    }

    public static function get($arg, $default = null)
    {
        $env = getEnv(strtoupper(str_replace(["-", "."], ["_", "__"], $arg)));
        if ($env) {
            return $env;
        }
        if (array_key_exists($arg, self::$opts)) {
            $value = self::$opts[$arg];
	    return null == $value ? true : $value;
        }
        return $default;
    }
}
Options::init();
