<?php
use SuperFlyXXI\VideoConverter\Input\Stream;
use SuperFlyXXI\VideoConverter\Requests\Request;

require_once "InputFile.php";

interface FFmpegArgGenerator
{
    public function getStreams(InputFile $inputFile);

    public function getAdditionalArgs($outTrack, Request $request, $inputTrack, Stream $stream);
}
