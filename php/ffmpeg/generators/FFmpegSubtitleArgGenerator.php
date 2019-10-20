<?php
include_once "ffmpeg/generators/FFmpegArgGenerator.php";
include_once "InputFile.php";
include_once "Request.php";
include_once "Stream.php";

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
        return $inputFile->getVideoStreams();
    }
}
?>
