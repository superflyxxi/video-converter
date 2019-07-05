<?php

include_once "Stream.php";

class InputFile {

	public function __construct($json) {
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

	private $streams = array();
	private $subtitleStreams = array();
	private $videoStreams = array();
	private $audioStreams = array();
	private $filename = NULL;

}

?>

