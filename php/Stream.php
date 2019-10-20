<?php

class Stream
{

    public function __construct($json)
    {
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
    }

    public $codec_type = NULL;

    public $codec_name = NULL;

    public $index = 0;

    public $language = NULL;

    public $channel_layout = NULL;

    public $channels = NULL;

    public $audio_sample_rate = NULL;
}
