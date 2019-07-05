<?php

class Stream {

	public function __construct($json) {
		$this->codec_type = $json["codec_type"];
		$this->codec_name = $json["codec_name"];
	}

	public $codec_type = NULL;
	public $codec_name = NULL;
}

