<?php

require_once "ffmpeg/generators/FFmpegArgGenerator.php";
require_once "InputFile.php";
require_once "request/Request.php";
require_once "Stream.php";

class FFmpegSubtitleArgGenerator implements FFmpegArgGenerator {
	public function getAdditionalArgs($outTrack, Request $request, $inputTrack, Stream $stream) {
		$args = " -c:s:" . $outTrack . " " . $request->subtitleFormat;
		$args .= " -metadata:s:s:" . $outTrack . " language=" . $stream->language;
		return $args;
	}

	public function getStreams(InputFile $inputFile) {
		return $inputFile->getSubtitleStreams();
	}
}
