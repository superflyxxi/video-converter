<?php
require_once "LogWrapper.php";

class Options
{
    private static $opts;

    public static function init()
    {
        self::$opts = getopt("", ["log-level::"]);
    }

    public static function get($arg, $default)
    {
        $env = getEnv(strtoupper(str_replace(["-", "."], ["_", "__"], $arg)));
        if ($env) {
            return $env;
        }
        if (array_key_exists($arg, self::$opts)) {
            return self::$opts[$arg];
        }
        return $default;
    }
}
Options::init();
?>
