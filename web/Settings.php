<?php

class Settings {
	private $settings;
	public function __construct() {
		$this->settings = array_merge(parse_ini_file("defaults.ini"), parse_ini_file("settings.ini"));
	}

	public static function getRootDirectory() {
		return $this->settings["root.directory"];
	}

}

?>

