<?php
include_once "Logger.php";
include_once "functions.php";
include_once "Request.php";
include_once "Stream.php";

class NormalizeAudio
{

    public static function normalize($oRequest)
    {
        $arrAdditionalRequests = array();
        if ("copy" != $oRequest->audioFormat && count($oRequest->normalizeAudioTracks)) {
            // only do this there are tracks to normalize
            $dir = getEnvWithDefault("TMP_DIR", "/tmp");
            // any track that is not needed, just copy it to its own file
            foreach ($oRequest->oInputFile->getAudioStreams() as $index => $stream) {
                // copy original always and add to list of additional requests
                $tmpRequest = new Request($oRequest->oInputFile->getFileName());
		$tmpRequest->setVideoTracks(NULL);
		$tmpRequest->setAudioTracks($index);
		$tmpRequest->setSubtitleTracks(NULL);
                $tmpRequest->audioFormat = $oRequest->audioFormat;
                $tmpRequest->audioQuality = $oRequest->audioQuality;
		$tmpRequest->audioChannelLayout = $oRequest->audioChannelLayout;
		$tmpRequest->setAudioChannelLayoutTracks(implode(" ", $oRequest->getAudioChannelLayoutTracks()));
                $tmpRequest->prepareStreams();
                if ($oRequest->oInputFile->getPrefix() != NULL) {
                    $origOutFile = new OutputFile($dir . realpath($oRequest->oInputFile->getFileName()) . '/dir-' . $index . '-orig.mkv');
                } else {
                    $origOutFile = new OutputFile($dir . $oRequest->oInputFile->getFileName() . '-' . $index . '-orig.mkv');
                }
                FFmpegHelper::execute(array(
                    $tmpRequest
                ), $origOutFile);
                $oNewRequest = new Request($origOutFile->getFileName());
		$oNewRequest->setVideoTracks(NULL);
		$oNewRequest->setSubtitleTracks(NULL);
	        $oNewRequest->setAudioTracks("0");
                $oNewRequest->audioFormat = "copy";
                $oNewRequest->prepareStreams();
                $arrAdditionalRequests[] = $oNewRequest;
                $oRequest->oInputFile->removeAudioStream($index);
                
                if (in_array($index, $oRequest->normalizeAudioTracks)) {
                    // if the track is to be normalized, now let's normalize it and put it in
                    Logger::info("Normalizing track {}:{}", array(
                        $oRequest->oInputFile->getFileName(),
                        $index
                    ));
                    $command = 'ffmpeg -hide_banner -i "' . $origOutFile->getFileName() . '" -map 0 -filter:a loudnorm=print_format=json -f null - 2>&1';
                    Logger::info("Measuring {}:{} with command: {}", array(
                        $oRequest->oInputFile->getFileName(),
                        $index,
                        $command
                    ));
                    exec($command, $out, $return);
                    Logger::verbose($out);
                    if ($return != 0) {
                        Logger::error("Normalizing failed: {}", array(
                            $return
                        ));
                        exit($return);
                    }
                    $out = implode(array_slice($out, - 12));
                    $json = json_decode($out, true);
                    
                    $normFile = $dir . $oRequest->oInputFile->getFileName() . '-' . $index . '-norm.mkv';
			$normChannelMap = ($oRequest->areAllAudioChannelLayoutTracksConsidered() || in_array($index, $oRequest->getAudioChannelLayoutTracks()))
				? $oRequest->audioChannelLayout
				: $stream->channel_layout;
                    
                    $normChannelMap = preg_replace("/\(.+\)/", '', $normChannelMap);
                    $command = 'ffmpeg -i "' . $origOutFile->getFileName() . '" -y -map 0' . ' -filter:a "loudnorm=measured_I=' . $json["input_i"] . ':measured_TP=' . $json["input_tp"] . ':measured_LRA=' . $json["input_lra"] . ':measured_thresh=' . $json["input_thresh"] . (NULL != $normChannelMap ? ',channelmap=channel_layout=' . $normChannelMap : '') . '" ' . ' -c:a ' . $oRequest->audioFormat . ' -q:a ' . $oRequest->audioQuality . ' -metadata:s:a:0 "title=Normalized ' . $stream->language . ' ' . $normChannelMap . '"' . ' -f matroska "' . $normFile . '" 2>&1';
                    
                    Logger::info("Normalizing {}:{} with command: {}", array(
                        $oRequest->oInputFile->getFileName(),
                        $index,
                        $command
                    ));
                    passthru($command, $return);
                    if ($return != 0) {
                        Logger::error("Normalizing failed: {}", array(
                            $return
                        ));
                        exit($return);
                    }
                    $oNewRequest = new Request($normFile);
                    $oNewRequest->audioTrack = 0;
                    $oNewRequest->audioFormat = "copy";
                    $arrAdditionalRequests[] = $oNewRequest;
                }
            }
        }
        return $arrAdditionalRequests;
    }
}

?>
