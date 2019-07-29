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
            printf("%s::%s::%s\n", date(self::dateformat), $reqlevel, $str);
        }
    }

    private static function init()
    {
        if (self::$loglevel == - 1) {
            date_default_timezone_set("UTC");
            switch (getEnv("LOG_LEVEL")) {
                case "WARN":
                    self::$loglevel = self::WARN;
                    break;
                case "ERROR":
                    self::$loglevel = self::ERROR;
                    break;
                case "VERBOSE":
                    self::$loglevel = self::VERBOSE;
                    break;
                case "DEBUG":
                    self::$loglevel = self::DEBUG;
                    break;
                default:
                    self::$loglevel = self::INFO;
            }
        }
    }
}

?>
