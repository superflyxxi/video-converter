<?php
namespace SuperFlyXXI\VideoConverter\Generators\ffmpeg;

use SuperFlyXXI\VideoConverter\Input\Stream;
use SuperFlyXXI\VideoConverter\Requests\Request;
use SuperFlyXXI\VideoConverter\Input\InputFile;

class FFmpegVideoArgGenerator implements FFmpegArgGenerator
{
    public function getAdditionalArgs($outTrack, Request $request, $inputTrack, Stream $stream)
    {
        $args = " -c:v:" . $outTrack;
        if ("copy" == $request->videoFormat) {
            $args .= " copy";
        } elseif ($request->isHDR()) {
            $args .= " libx265 -crf 20 -level:v 51 -pix_fmt yuv420p10le -color_primaries 9"
                . " -color_trc 16 -colorspace 9 -color_range 1 -profile:v main10";
        } elseif ($request->isHwAccelEncode()) {
            $filters = "";
            if ($request->deinterlace) {
                switch ($request->deinterlaceMode) {
                    default:
                    case "00":
                        $filters .= ",hwdownload,dejudder,fps=" . $stream->frame_rate .
                            ",fieldmatch,yadif=deint=interlaced,decimate,hwupload";
                        // https://ffmpeg.org/ffmpeg-filters.html#fieldmatch
                        break;
                    case "01":
                        // each field is a frame (double framerate)
                        // https://www.mltframework.org/plugins/FilterAvfilter-deinterlace_vaapi/
                        $filters .= ",deinterlace_vaapi=rate=field:auto=1";
                        break;
                    case "02":
                        $filters .= ",deinterlace_vaapi";
                        break;
                }
            }
            if ($request->videoUpscale != 1) {
                $filters .= ",scale_vaapi=w=" . $request->videoUpscale * $stream->width . ":h=" .
                    $request->videoUpscale * $stream->height;
            }
            if (strlen($filters) > 0) {
                $args = ' -vf "' . substr($filters, 1) . '"' . $args;
            }
            $args .= " " . $request->videoFormat . " -qp 20 -level:v 4";
        } else {
            $filters = "";
            if ($request->deinterlace) {
                switch ($request->deinterlaceMode) {
                    default:
                    case "00":
                        $filters .= ",dejudder,fps=" . $stream->frame_rate
                            . ",fieldmatch,yadif=deint=interlaced,decimate";
                        // https://ffmpeg.org/ffmpeg-filters.html#fieldmatch
                        break;
                    case "01":
                        $filters .= ",yadif=mode=1";
                        // each field is a frame (double framerate) https://ffmpeg.org/ffmpeg-filters.html#yadif-1
                        break;
                    case "02":
                        $filters .= ",yadif"; // original
                        break;
                }
            }
            if ($request->videoUpscale != 1) {
                $filters .= ",scale=" . $request->videoUpscale * $stream->width . ":" .
                    $request->videoUpscale * $stream->height;
            }
            if (strlen($filters) > 0) {
                $args = ' -vf "' . substr($filters, 1) . '"' . $args;
            }
            $args .= " " . $request->videoFormat . " -crf 20 -level:v 4";
        }
        $args .= " -metadata:s:v:" . $outTrack . " language=" . $stream->language;
        return $args;
    }

    public function getStreams(InputFile $inputFile)
    {
        return $inputFile->getVideoStreams();
    }
}
