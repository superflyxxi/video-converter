<?php

class Request {

	public function __construct($filename) {
		$this->filename = $filename;
		if (is_dir($this->filename) || substr($this->filename, -strlen($this->filename)) === ".iso") {
			$this->prefix = "bluray:";
		}
	}

	private $filename = NULL;
	private $prefix = NULL;
	public $playlist = NULL;
	public $subtitleTrack = NULL;
	public $subtitleFormat = NULL;
	public $audioFormat = NULL;
	public $audioTrack = NULL;
	public $audioQuality = NULL;
	public $audioChannelMappingTracks = NULL;
}

?>

