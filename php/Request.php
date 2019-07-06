<?php

include_once "InputFile.php";

class Request {

	public function __construct($filename) {
		$this->oInputFile = new InputFile($filename);
		if (is_dir($this->oInputFile->getFileName()) 
				|| substr($this->oInputFile->getFileName(), -strlen($this->oInputFile->getFileName())) === ".iso") {
			$this->prefix = "bluray:";
		}
		
		$this->hwaccel = is_dir("/dev/dri");
	}

	public $oInputFile = NULL;
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

