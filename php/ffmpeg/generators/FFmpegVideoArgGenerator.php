<?php
require_once "ffmpeg/generators/FFmpegArgGenerator.php";
require_once "InputFile.php";
require_once "request/Request.php";
require_once "Stream.php";

class FFmpegVideoArgGenerator implements FFmpegArgGenerator
{

    public function getAdditionalArgs($outTrack, Request $request, $inputTrack, Stream $stream)
    {
        $args = " ";
        if ("copy" == $request->videoFormat) {
            $args .= " -c:v:" . $outTrack . " copy";
        } else if ($request->isHDR()) {
            $args .= " -c:v:" . $outTrack . " libx265 -crf 20 -level:v 51 -pix_fmt yuv420p10le -color_primaries 9 -color_trc 16 -colorspace 9 -color_range 1 -profile:v main10";
        } else if ($request->isHwaccel()) {
            if ($request->deinterlace) {
                switch($request->deinterlaceMode) {
                    default:
                    case "00":
                        $args .= " -vf 'hwdownload,dejudder,fps=" . $stream->frame_rate .",fieldmatch,yadif=deint=interlaced,decimate,hwupload'"; // https://ffmpeg.org/ffmpeg-filters.html#fieldmatch
                        break;
                    case "01":
                        // each field is a frame (double framerate) https://www.mltframework.org/plugins/FilterAvfilter-deinterlace_vaapi/
                        $args .= " -vf 'deinterlace_vaapi=rate=field:auto=1'"; 
                        break;
                    case "02":
                        $args .= " -vf deinterlace_vaapi";
                        break;
                }
            }
	    if ($request->videoUpscale != 1) {
		$args .= " -vf 'scale_vaapi=w=1280:h=720'";
	    }
            $args .= " -c:v:" . $outTrack . " hevc_vaapi -qp 20 -level:v 4";
        } else {
            if ($request->deinterlace) {
                switch($request->deinterlaceMode) {
                    default:
                    case "00":
                        $args .= " -vf 'dejudder,fps=" . $stream->frame_rate . ",fieldmatch,yadif=deint=interlaced',decimate"; // https://ffmpeg.org/ffmpeg-filters.html#fieldmatch
                        break;
                    case "01":
                        $args .= " -vf 'yadif=mode=1'"; // each field is a frame (double framerate) https://ffmpeg.org/ffmpeg-filters.html#yadif-1
                        break;
                    case "02":
                        $args .= " -vf yadif"; // original
                        break;
                }
            }
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
