<?php
namespace SuperFlyXXI\VideoConverter\Generators\ffmpeg;

use SuperFlyXXI\VideoConverter\Input\InputFile;
use SuperFlyXXI\VideoConverter\Input\Stream;
use SuperFlyXXI\VideoConverter\Requests\Request;

interface FFmpegArgGenerator
{
    public function getStreams(InputFile $inputFile);

    public function getAdditionalArgs($outTrack, Request $request, $inputTrack, Stream $stream);
}
