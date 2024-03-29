#!/usr/bin/php
<?php
use SuperFlyXXI\VideoConverter\RipVideo;

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
require_once __DIR__ . "/../vendor/autoload.php";

function errorHandler(int $errno, string $errstr, $errfile = null, $errline = 0, $errcontext = null)
{
    print_r("Error encountered! ");
    print_r($errstr);
    print_r(" at file ");
    print_r($errfile);
    print_r(":");
    print_r($errline);
    print_r("\n");
    print_r($errcontext);
    ob_flush();
    flush();
    exit($errno);
}
set_error_handler("errorHandler");

$rip = new RipVideo();
exit($rip->rip());
