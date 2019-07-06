<?php

class Stream {

	public function __construct($json) {
		$this->index = $json["index"];
		$this->codec_type = $json["codec_type"];
		$this->codec_name = $json["codec_name"];
		if (array_key_exists("tags", $json) && array_key_exists("language", $json["tags"])) {
			$this->language = $json["tags"]["language"];
		}
	}

	public $codec_type = NULL;
	public $codec_name = NULL;
	public $index = 0;
	public $language = NULL;
}

