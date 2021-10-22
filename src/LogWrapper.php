<?php

require_once "functions.php";
require_once "Options.php";

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LogWrapper extends Logger {
	public function __construct($name) {
		parent::__construct($name);
		$this->pushHandler(new StreamHandler("php://stdout", Options::get("log-level", Logger::NOTICE)));
	}
}
