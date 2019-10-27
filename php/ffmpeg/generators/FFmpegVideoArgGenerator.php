<?php
include_once "ffmpeg/generators/FFmpegArgGenerator.php";
include_once "InputFile.php";
include_once "Request.php";
include_once "Stream.php";

class FFmpegVideoArgGenerator implements FFmpegArgGenerator
{

    public function getAdditionalArgs($outTrack, Request $request, $inputTrack, Stream $stream)
    {
	$args = " ";
        if ("copy" == $request->videoFormat) {
            $args .= " -c:v:" . $outTrack . " copy";
        } else if ($request->isHDR()) {
            $args .= " -c:v:" . $outTrack . " libx265 -crf 20 -level:v 4 -pix_fmt yuv420p10le -color_primaries 9 -color_trc 16 -colorspace 9 -color_range 1 -profile:v main10";
        } else if ($request->isHwaccel()) {
            $args .= " -c:v:" . $outTrack . " hevc_vaapi -qp 20 -level:v 4";
            if ($request->deinterlace) {
                $args .= " -vf deinterlace_vaapi=rate=field:auto=1";
            }
        } else {
            $args .= " -c:v:" . $outTrack . " libx265 -crf 20 -level:v 4";
        }
        $args .= " -metadata:s:v:" . $outTrack . " language=" . $stream->language;
        return $args;
    }

    public function getStreams(InputFile $inputFile)
    {
        return $inputFile->getVideoStreams();
    }
}
?>
