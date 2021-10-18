<?php

class Options {
	private static $opts;

	public static function init() {
		self::$opts = getopt("", [
			"log-level::",
			"input::",
			"title:",
			"year::",
			"season::",
			"episode::",
			"show-title::",
			"video-tracks::",
			"video-format::",
			"video-upscale::",
			"deinterlace::",
			"deinterlace-check::",
			"audio-tracks::",
			"audio-format::",
			"audio-quality::",
			"audio-sample-rate::",
			"audio-channel-layout::",
			"audio-channel-layout-tracks::",
			"normalize-audio-tracks::",
			"subtitle-tracks::",
			"subtitle-format::",
			"subtitle-conversion-output::",
			"subtitle-conversion-blacklist::",
			"hdr",
			"disable-postfix",
			"playlist::",
		]);
	}

	public static function get($arg, $default = null) {
		$env = getEnv(strtoupper(str_replace(["-", "."], ["_", "__"], $arg)));
		if ($env) {
			return $env;
		}
		if (array_key_exists($arg, self::$opts)) {
			$value = self::$opts[$arg];
			if ($value == null) {
				return true;
			}
			return $value;
		}
		return $default;
	}
}
Options::init();
?>