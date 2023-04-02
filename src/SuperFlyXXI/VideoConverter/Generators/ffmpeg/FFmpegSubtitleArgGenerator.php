<?php
namespace SuperFlyXXI\VideoConverter\Generators\ffmpeg;

use SuperFlyXXI\VideoConverter\Input\Stream;
use SuperFlyXXI\VideoConverter\Requests\Request;
use SuperFlyXXI\VideoConverter\Input\InputFile;

class FFmpegSubtitleArgGenerator implements FFmpegArgGenerator
{
    public function getAdditionalArgs($outTrack, Request $request, $inputTrack, Stream $stream)
    {
        $args = " -c:s:" . $outTrack . " " . $request->subtitleFormat;
        $args .= " -metadata:s:s:" . $outTrack . " language=" . $stream->language;
        return $args;
    }

    public function getStreams(InputFile $inputFile)
    {
        return $inputFile->getSubtitleStreams();
    }
}