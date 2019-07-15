<?php

include_once "Logger.php";
include_once "functions.php";
include_once "Request.php";
include_once "Stream.php";

class NormalizeAudio {
    
    public static function normalize($oRequest) {
        $arrAdditionalRequests = array();
        if ("copy" != $oRequest->audioFormat) {
            $dir = getEnvWithDefault("TMP_DIR", "/tmp");
            foreach ($oRequest->normalizeAudioTracks as $index) {
		if (is_numeric($index)) {
			Logger::info("Normalizing track {}:{}", array($oRequest->oInputFile->getFileName(), $index));
	                $stream = $oRequest->oInputFile->getAudioStreams()[$index];

			$tmpRequest = new Request($oRequest->oInputFile->getFileName());
			$tmpRequest->audioTrack = $index;
			$tmpRequest->audioFormat = $oRequest->audioFormat;
			$tmpRequest->audioQuality = $oRequest->audioQuality;
			$tmpRequest->audioChannelMapping = $oRequest->audioChannelMapping;
			$tmpRequest->prepareStreams();
			if ($oRequest->oInputFile->getPrefix() != NULL) {
				$origOutFile = new OutputFile($dir.realpath($oRequest->oInputFile->getFileName()).'/dir-'.$index.'-orig.mkv');
			} else {
				$origOutFile = new OutputFile($dir.$oRequest->oInputFile->getFileName().'-'.$index.'-orig.mkv');
			}
			FFmpegHelper::execute(array($tmpRequest), $origOutFile);
			
                	$oNewRequest = new Request($origOutFile->getFileName());
	                $oNewRequest->audioTrack = 0;
			$oNewRequest->audioFormat= "copy";
			$oNewRequest->prepareStreams();
                	$arrAdditionalRequests[] = $oNewRequest;
			$oRequest->oInputFile->removeAudioStream($index);

	                $command = 'ffmpeg -hide_banner -i "'.$origOutFile->getFileName()
        	                .'" -map 0 -filter:a loudnorm=print_format=json -f null - 2>&1';
	                Logger::info("Measuring {}:{} with command: {}", array($oRequest->oInputFile->getFileName(), $index, $command));
        	        exec($command, $out, $return);
			Logger::verbose($out);
        	        if ($return != 0) {
                	    Logger::error("Normalizing failed: {}", array($return));
	                    exit($return);
	                }
        	        $out = implode(array_slice($out, -12));
        	        $json = json_decode($out, true);
                
        	        $normFile = $dir.$oRequest->oInputFile->getFileName().'-'.$index.'-norm.mkv';
			$normChannelMap = array_key_exists($index, $oRequest->audioChannelMapping) 
				? $oRequest->audioChannelMapping[$index] 
				: $stream->channel_layout;

			$normChannelMap = preg_replace("/\(.+\)/", '', $normChannelMap);
                	$command = 'ffmpeg -i "'.$origOutFile->getFileName().'" -y -map 0'
        	                .' -filter:a "loudnorm=measured_I='.$json["input_i"]
                	        .':measured_TP='.$json["input_tp"]
                        	.':measured_LRA='.$json["input_lra"]
	                        .':measured_thresh='.$json["input_thresh"] 
				.(NULL != $normChannelMap ? ',channelmap=channel_layout='.$normChannelMap : '')
				.'" '
                	        .' -c:a '.$oRequest->audioFormat
                        	.' -q:a '.$oRequest->audioQuality
	                        .' -metadata:s:a:0 "title=Normalized '.$stream->language.' '.$normChannelMap.'"'
        	                .' -f matroska "'.$normFile.'" 2>&1';

	                Logger::info("Normalizing {}:{} with command: {}", array($oRequest->oInputFile->getFileName(), $index, $command));
        	        passthru($command, $return);
        	        if ($return != 0) {
                	    Logger::error("Normalizing failed: {}", array($return));
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
