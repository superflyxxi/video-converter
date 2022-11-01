<?php

require_once "InputFile.php";
require_once "request/Request.php";
require_once "Stream.php";

interface FFmpegArgGenerator
{
	public function getStreams(InputFile $inputFile);

	public function getAdditionalArgs($outTrack, Request $request, $inputTrack, Stream $stream);
}
