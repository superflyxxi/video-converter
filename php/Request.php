<?php

include_once "InputFile.php";
include_once "functions.php";

class Request {

	public function __construct($filename) {
		$this->oInputFile = new InputFile($filename);
		$this->hwaccel = is_dir("/dev/dri");
		$this->videoHdr = getEnvWithDefault("HDR", "false") == "true";
	}

	public static function newInstanceFromEnv($filename) {
		$req = new Request($filename);
		
		$req->setVideoTracks(getEnvWithDefault("VIDEO_TRACKS", "*"));
		$req->playlist = getEnv("PLAYLIST");
		$req->setSubtitleTracks(getEnvWithDefault("SUBTITLE_TRACKS", "*"));
		$req->subtitleFormat = getEnvWithDefault("SUBTITLE_FORMAT", "ass");

		$req->setAudioTracks(getEnvWithDefault("AUDIO_TRACK", "*"));
		$req->audioFormat = getEnvWithDefault("AUDIO_FORMAT", "aac");
		$req->audioQuality = getEnvWithDefault("AUDIO_QUALITY", "2");
		$req->normalizeAudioTracks = explode(" ", getEnvWIthDefault("NORMALIZE_AUDIO_TRACKS", ""));
		$mapping = getEnvWithDefault("AUDIO_CHANNEL_LAYOUT", "5.1");
		foreach (explode(" ", getEnvWithDefault("AUDIO_CHANNEL_MAPPING_TRACKS", "")) as $track) {
			$req->audioChannelMapping[$track] = $mapping;
		}

		$req->deinterlace = ("true" == getEnvWithDefault("DEINTERLACE", "false"));

		$req->videoTrack = getEnvWithDefault("VIDEO_TRACK", "v");
		$req->videoFormat = getEnvWithDefault("VIDEO_FORMAT", "notcopy");

		$req->prepareStreams();
		return $req;
	}

	public function setAudioTracks($req) {
		$this->audioTracks = explode(' ', $req);
	}

	public function areAllAudioTracksConsidered() {
		return in_array("*", $this->audioTracks);
	}

	public function getAduioTracks() {
		return $this->audioTracks;
	}

	public function setVideoTracks($req) {
		$this->videoTracks = explode(' ', $req);
	}

	public function areAllVideoTracksConsidered() {
		return in_array("*", $this->videoTracks);
	}

	public function getVideoTracks() {
		return $this->videoTracks;
	}

	public function setSubtitleTracks($req) {
		$this->subtitleTracks = explode(' ', $req);
	}

	public function areAllSubtitleTracksConsidered() {
		return in_array("*", $this->subtitleTracks);
	}

	public function getSubtitleTracks() {
		return $this->subtitleTracks;
	}

	public function prepareStreams() {
		if (!$this->areAllSubtitleTracksConsidered()) {
			// if not * (all subtitles), then remove all track except the desired
			foreach ($this->oInputFile->getSubtitleStreams() as $track) {
				if (!in_array($track->index, $this->getSubtitleTracks())) {
					$this->oInputFile->removeSubtitleStream($track->index);
				}
			}
		}
		if (!$this->areAllAudioTracksConsidered()) {
			// if not * (all audio), then remove all track except the desired
			foreach ($this->oInputFile->getAudioStreams() as $track) {
				if ($this->audioTrack != $track->index) {
					$this->oInputFile->removeAudioStream($track->index);
				}
			}
		}
		if (!$this->areAllVideoTracksConsidered()) {
			// if not * (all videos), then remove all track except the desired
			foreach ($this->oInputFile->getVideoStreams() as $track) {
				if (!in_array($track->index, $this->getVideoTracks())) {
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
	public $playlist = NULL;
	private $subtitleTracks = array("*");
	public $subtitleFormat = NULL;
	private $audioTracks = array("*");
	public $audioFormat = NULL;
	public $audioQuality = NULL;
	public $audioChannelMapping = NULL;
	public $normalizeAudioTracks = NULL;
	private $hwaccel = false;
	public $deinterlace = false;
	private $videoTracks = array("*");
	public $videoFormat = NULL;
	private $videoHdr = false;
}

?>

