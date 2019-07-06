<?php

include_once "Stream.php";

class InputFile {

	public function __construct($filename) {
		$this->filename = $filename;
		$command = 'ffprobe -v quiet -print_format json -show_format -show_streams "'.$this->filename.'"';
		exec($command, $out);
		#print("\nffprobe Output: "); print_r(implode($out));
		$json = json_decode(implode($out), true);
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

	public function getFileName() {
		return $this->filename;
	}

}

?>

