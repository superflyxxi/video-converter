<?php
use SuperFlyXXI\VideoConverter\Input\Stream;

require_once "InputFile.php";
require_once "request/Request.php";

interface FFmpegArgGenerator
{
    public function getStreams(InputFile $inputFile);

    public function getAdditionalArgs($outTrack, Request $request, $inputTrack, Stream $stream);
}
