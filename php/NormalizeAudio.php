<?php

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
	                $command = 'ffmpeg -hide_banner -i "'.$oRequest->oInputFile->getFileName()
        	                .'" -map 0:'.$index.' -filter:a loudnorm=print_format=json -f null - ';
	                printf("Measuring %s with command: %s\n", $index, $command);
        	        exec($command, $out, $return);
                	printf("Output: \n");
			print_r($out);
        	        if ($return != 0) {
                	    printf("Normalizing failed: %s\n", $return);
	                    exit($return);
	                }
        	        $json = json_decode(implode($out), true);
			printf("JSON:\n");
	                print_r($json);
        
	                $stream = $oRequest->oInputFile->getAudioStreams()[$index];
                
        	        $tmpFile = $dir.$oRequest->oInputFile->getFileName().'-'.$index.'-norm.mkv';
                	$command = 'ffmpeg -i "'.$oRequest->oInputFile->getFileName()
	                        .'" -y -map 0:'.$index
        	                .' -filter:a loudnorm=measured_I='.$json["input_i"]
                	        .':measured_TP='.$json["input_tp"]
                        	.':measured_LRA='.$json["input_lra"]
	                        .':measured_thresh='.$json["input_thresh"] 
				.(NULL != $stream->channel_layout ? ' channelmap=channel_layout='.$stream->channel_layout : ' ')
                	        .' -c:a '.$oRequest->audioFormat
                        	.' -q:a '.$oRequest->audioQuality
	                        .' -metadata:s:a:0 "title=Normalized '.$stream->language.' '.$stream->channel_layout.'"'
        	                .' -f matroska "'.$tmpFile.'"';
                	$oNewRequest = new Request($tmpFile);
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
