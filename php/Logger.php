<?php
require_once 'functions.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$log = new Logger('video-converter');
$log->pushHandler(new StreamHandler('php://stdout', getEnvWithDefault("LOG_LEVEL", Logger::NOTICE)));

class Logger
{

    public static function info()
    {
        $log->info(func_get_args());
    }

    public static function warn()
    {
        $log->warn(func_get_args());
    }

    public static function error()
    {
        $log->error(func_get_args());
    }

    public static function verbose()
    {
        $log->debug(func_get_args());
    }

    public static function debug()
    {
        $log->debug(func_get_args());
    }
}

?>
