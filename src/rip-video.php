#!/usr/bin/php
<?php
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
require_once __DIR__ . "/../vendor/autoload.php";

require_once "index.php";
exit(rip());
