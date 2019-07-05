<?php

include_once "Stream.php";

class InputFile {

	public function __construct($filename) {
		$this->filename = $filename;
		if (is_dir($this->filename) || substr($this->filename, -strlen($this->filename)) === ".iso") {
			$this->prefix = "bluray:";
		}
	}

	public function readProbe($json) {
		$this->filename = $json["format"]["filename"];
		foreach ($json["streams"] as $stream) {
			$oStream = new Stream($stream);
			$this->streams[$oStream->index] = $oStream;
			switch ($oStream->codec_type) {
				case "video":
					$this->videoStreams[$oStream->index] = $oStream;
					break;
				case "audio":
					$this->audioStreams[$oStream->index] = $oStream;
					break;
				case "subtitle":
					$this->subtitleStreams[$oStream->index] = $oStream;
					break;
				default:
			}
		}
	}

	public $streams = array();
	public $subtitleStreams = array();
	public $videoStreams = array();
	public $audioStreams = array();
	public $filename = NULL;
	private $prefix = NULL;

}

?>

