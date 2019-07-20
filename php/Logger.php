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

    public static function info($msg, array $args = array())
    {
        self::log(self::INFO, $msg, $args);
    }

    public static function warn($msg, array $args = array())
    {
        self::log(self::WARN, $msg, $args);
    }

    public static function error($msg, array $args = array())
    {
        self::log(self::ERROR, $msg, $args);
    }

    public static function verbose($msg, array $args = array())
    {
        self::log(self::VERBOSE, $msg, $args);
    }

    public static function debug($msg, array $args = array())
    {
        self::log(self::DEBUG, $msg, $args);
    }

    public static function log($reqlevel, $msg, array $args)
    {
        self::init();
        if (self::$loglevel >= $reqlevel) {
            $str = print_r($msg, true);
            foreach ($args as $arg) {
                $str = preg_replace("/{}/", print_r($arg, true), $str, 1);
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
