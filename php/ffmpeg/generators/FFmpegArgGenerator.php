<?php
include_once "InputFile.php";
include_once "Request.php";
include_once "Stream.php";

interface FFmpegArgGenerator
{

    public function getStreams(InputFile $inputFile);

    public function getAdditionalArgs($outTrack, Request $request, Stream $stream);
}
?>
