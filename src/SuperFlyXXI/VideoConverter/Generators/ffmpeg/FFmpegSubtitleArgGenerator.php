<?php
namespace SuperFlyXXI\VideoConverter\Generators\ffmpeg;

use SuperFlyXXI\VideoConverter\Input\Stream;
use SuperFlyXXI\VideoConverter\Requests\Request;
use SuperFlyXXI\VideoConverter\Input\InputFile;

class FFmpegSubtitleArgGenerator implements FFmpegArgGenerator
{
    public function getAdditionalArgs($typeOutTrack, Request $request, $index, $typeInputTrack, Stream $stream)
    {
        $args = " -c:s:" . $typeOutTrack . " " . $request->subtitleFormat;
        $args .= " -metadata:s:s:" . $typeOutTrack . " language=" . $stream->language;
        return $args;
    }

    public function getStreams(InputFile $inputFile)
    {
        return $inputFile->getSubtitleStreams();
    }
}
