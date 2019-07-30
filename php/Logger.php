<?php
class Logger
{

    const VERBOSE = 0;

    const DEBUG = 1;

    const WARN = 2;

    const ERROR = 3;

    const INFO = 4;

    const dateformat = "c";

    private static $loglevel = - 1;

    private static $mapLogNumToString = NULL;

    private static function init()
    {
        if (self::$loglevel == - 1) {
            date_default_timezone_set("UTC");
            self::$mapLogNumToString[self::VERBOSE] = "VERBOSE";
            self::$mapLogNumToString[self::DEBUG] = "DEBUG";
            self::$mapLogNumToString[self::WARN] = "WARN";
            self::$mapLogNumToString[self::ERROR] = "ERROR";
            self::$mapLogNumToString[self::INFO] = "INFO";
            self::$loglevel = array_search(getEnvWithDefault("LOG_LEVEL", "INFO"), self::$mapLogNumToString);
            if (self::$loglevel === FALSE) {
                self::$loglevel = self::INFO;
                warn("Invalid Log Level set for logging.");
            }
        }
    }

    public static function info()
    {
        self::log(self::INFO, func_get_args());
    }

    public static function warn()
    {
        self::log(self::WARN, func_get_args());
    }

    public static function error()
    {
        self::log(self::ERROR, func_get_args());
    }

    public static function verbose()
    {
        self::log(self::VERBOSE, func_get_args());
    }

    public static function debug()
    {
        self::log(self::DEBUG, func_get_args());
    }

    public static function log($reqlevel, array $args)
    {
        self::init();
        if (self::$loglevel >= $reqlevel) {
            $str = print_r($args[0], true);
            for ($i = 1, $count = count($args); $i < $count; $i ++) {
                $str = preg_replace("/{}/", print_r($args[$i], true), $str, 1);
            }
            printf("%s::%s::%s\n", date(self::dateformat), self::$mapLogNumToString[$reqlevel], $str);
        }
    }
}

?>
