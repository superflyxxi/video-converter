<?php
require_once "LogWrapper.php";

class ConvertVideo {
	public static $log;

	public static function convert($oRequest): array {
		return [];
	}
}

ConvertVideo::$log = new LogWrapper("ConvertVideo");
?>
