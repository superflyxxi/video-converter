<?php

class Request {

	public function __construct($filename) {
		$this->file = $filename;
		if (is_dir($this->file) || substr($this->file, -strlen($this->file)) === ".iso") {
			$this->prefix = "bluray:";
		}
		$this->hwaccel = is_dir("/dev/dri");
	}

	public $file = NULL;
	private $prefix = NULL;
	public $playlist = NULL;
	public $subtitleTrack = NULL;
	public $subtitleFormat = NULL;
	public $audioFormat = NULL;
	public $audioTrack = NULL;
	public $audioQuality = NULL;
	public $audioChannelMappingTracks = NULL;
	private $hwaccel = false;
	public $deinterlace = false;
	public $videoFormat = NULL;
}

?>

