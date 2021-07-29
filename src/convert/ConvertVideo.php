<?php
require_once "request/Request.php";
require_once "LogWrapper.php";
require_once "OutputFile.php";

class ConvertVideo
{
    public static $log;

    public static function convert($oRequest): array {
        if ($oRequest->videoUpscale == 1) {
            return [];
        }
        if ($oRequest->videoUpscale > 4) {
            self::$log->warn("Can't upscale video more than 4.", array('factor'=>$oRquest->videoUpscale));
            return [];
        }
        $arrReq = array();
        $imgOutFilename = "/tmp/frames-%09d.jpg";
        {
            self::$log->info("Extracting images from video.", array('factor'=>$oRquest->videoUpscale));
            // extract video as images to {ESRGAN_DIR}/LR
            $imgOutFile = new OutputFile("/tmp", $imgOutFilename);
            $imgRequest = new Request($oRequest->oInputFile->getFileName());
            $imgRequest->setSubtitleTracks(NULL);
            $imgRequest->setAudioTracks(NULL);
            $imgRequest->setVideoTracks(0); // TODO don't assume 0
            $imgRequest->videoFormat = "extract";
            $imgRequest->prepareStreams();
            FFmpegHelper::execute(array($imgRequest), $imgOutFile, FALSE);
        }
        // run upscale
        {
            self::$log->info("Upscaling images. TODO");
            passthru("python3 ${ESRGAN_DIR}/upscale.py");
        }
        // combine into a video.mkv --convert as well
        {
            self::$log->info("Making new video. TODO");
            $dir = getEnvWithDefault("TMP_DIR", "/tmp");
            $vidOutFile = new OutputFile(NULL, $dir."/video.mkv");
            $vidRequest = new Request($imgOutFilename);
            $vidRequest->setSubtitleTracks(NULL);
            $vidRequest->setAudioTracks(NULL);
            $vidRequest->setVideoTracks(0); // TODO don't assume 0
            $vidRequest->videoFormat = $oRequest->videoFormat;
            $vidRequest->deinterlace = $oRequest->deinterlace;
            $vidRequest->deinterlaceMode = $oRequest->deinterlaceMode;
            $vidRequest->prepareStreams();
            FFmpegHelper::execute(array($vidRequest), $vidOutFile, FALSE);
        }
        // add video track as new request as copy and remove video request from original
        return $arrReq;
    }
}

ConvertVideo::$log = new LogWrapper('ConvertVideo');
?>
