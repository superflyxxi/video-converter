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
		
		$req->playlist = getEnv("PLAYLIST");
		$req->subtitleTrack = getEnvWithDefault("SUBTITLE_TRACK", "s?");
		$req->subtitleFormat = getEnvWithDefault("SUBTITLE_FORMAT", "ass");

		$req->audioTrack = getEnvWithDefault("AUDIO_TRACK", "a");
		$req->audioFormat = getEnvWithDefault("AUDIO_FORMAT", "aac");
		$req->audioQuality = getEnvWithDefault("AUDIO_QUALITY", "2");
		$req->normalizeAudioTracks = explode(" ", getEnvWIthDefault("NORMALIZE_AUDIO_TRACKS", ""));
		$mapping = getEnvWithDefault("AUDIO_CHANNEL_LAYOUT", "5.1");
		foreach (explode(" ", getEnvWithDefault("AUDIO_CHANNEL_MAPPING_TRACKS", "1")) as $track) {
			$req->audioChannelMapping[$track] = $mapping;
		}

		$req->deinterlace = ("true" == getEnvWithDefault("DEINTERLACE", "false"));

		$req->videoTrack = getEnvWithDefault("VIDEO_TRACK", "v");
		$req->videoFormat = getEnvWithDefault("VIDEO_FORMAT", "notcopy");

		$req->prepareStreams();
		return $req;
	}

	public function prepareStreams() {
		if (substr($this->subtitleTrack, 0, strlen("s")) !== "s") {
			// if not s (all subtitles), then remove all track except the desired
			foreach ($this->oInputFile->getSubtitleStreams() as $track) {
				if ($this->subtitleTrack == NULL || $this->subtitleTrack != $track->index) {
					$this->oInputFile->removeSubtitleStream($track->index);
				}
			}
		}
		if (substr($this->audioTrack, 0, strlen("a")) !== "a") {
			// if not a (all audio), then remove all track except the desired
			foreach ($this->oInputFile->getAudioStreams() as $track) {
				if ($this->audioTrack == NULL || $this->audioTrack != $track->index) {	
					$this->oInputFile->removeAudioStream($track->index);
				}
			}
		}
		if (substr($this->videoTrack, 0, strlen("v")) !== "v") {
			// if not v (all videos), then remove all track except the desired
			foreach ($this->oInputFile->getVideoStreams() as $track) {
				if ($this->videoTrack == NULL || $this->videoTrack != $track->index) {
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
	public $subtitleTrack = NULL;
	public $subtitleFormat = NULL;
	public $audioFormat = NULL;
	public $audioTrack = NULL;
	public $audioQuality = NULL;
	public $audioChannelMapping = NULL;
	public $normalizeAudioTracks = NULL;
	private $hwaccel = false;
	public $deinterlace = false;
	public $videoTrack = NULL;
	public $videoFormat = NULL;
	private $videoHdr = false;
}

?>

