<?php

include_once "InputFile.php";
include_once "functions.php";

class Request {

	public function __construct($filename) {
		$this->oInputFile = new InputFile($filename);
		if (is_dir($this->oInputFile->getFileName()) 
				|| substr($this->oInputFile->getFileName(), -strlen($this->oInputFile->getFileName())) === ".iso") {
			$this->prefix = "bluray:";
		}
		
		$this->hwaccel = is_dir("/dev/dri");
		$this->videoHdr = getEnvWithDefault("HDR", "false") == "true";
	}

	public function prepareStreams() {
		if (substr($this->subtitleTrack, 0, strlen("s")) !== "s") {
			// if not s (all subtitles), then remove all track except the desired
			foreach ($this->oInputFile->getSubtitleStreams() as $track) {
				if ($this->subtitleTrack != $track->index) {
					$this->oInputFile->removeSubtitleStream($track->index);
				}
			}
		}
		if (substr($this->audioTrack, 0, strlen("a")) !== "a") {
			// if not a (all audio), then remove all track except the desired
			foreach ($this->oInputFile->getAudioStreams() as $track) {
				if ($this->audioTrack != $track->index) {
					$this->oInputFile->removeAudioStream($track->index);
				}
			}
		}
		if (substr($this->videoTrack, 0, strlen("v")) !== "v") {
			// if not s (all subtitles), then remove all track except the desired
			foreach ($this->oInputFile->getVideoStreams() as $track) {
				if ($this->videoTrack != $track->index) {
					$this->oInputFile->removeVideoStream($track->index);
				}
			}
		}
	}

	public function isHwaccel() {
		return $this->hwaccel;
	}

	public function isHDR() {
		return $this->videoHdr;
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
	public $videoTrack = NULL;
	public $videoFormat = NULL;
	private $videoHdr = false;
}

?>

