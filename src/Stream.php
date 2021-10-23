<?php

class Stream {
	public function __construct($json) {
		$this->index = $json["index"];
		$this->codec_type = $json["codec_type"];
		$this->codec_name = $json["codec_name"];
		if (array_key_exists("tags", $json) && array_key_exists("language", $json["tags"])) {
			$this->language = $json["tags"]["language"];
		}
		if (array_key_exists("channel_layout", $json)) {
			$this->channel_layout = $json["channel_layout"];
		}
		if (array_key_exists("channels", $json)) {
			$this->channels = $json["channels"];
		}
		if (array_key_exists("codec_time_base", $json)) {
			$this->audio_sample_rate = substr($json["codec_time_base"], 2);
		}
		if (array_key_exists("r_frame_rate", $json)) {
			$this->frame_rate = $json["r_frame_rate"];
		}
		if (array_key_exists("height", $json)) {
			$this->height = $json["height"];
		}
		if (array_key_exists("width", $json)) {
			$this->width = $json["width"];
		}
	}

	public $codec_type = null;

	public $codec_name = null;

	public $index = 0;

	public $language = null;

	public $channel_layout = null;

	public $channels = null;

	public $audio_sample_rate = null;

	public $frame_rate = null;

	public $height = null;

	public $width = null;
}
