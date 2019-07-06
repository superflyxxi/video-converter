<?php

include_once "Request.php";
include_once "InputFile.php";
include_once "functions.php";

class SubtitleConvert {
	public static function convert($oRequest) {
		$arrAdditionalRequests = array();
		if ($oRequest->subtitleFormat != "copy") {
			$filename = $oRequest->oInputFile->getFileName();
			foreach ($oRequest->oInputFile->getSubtitleStreams() as $index => $subtitle) {
				$codecName = $subtitle->codec_name;
				$dvdFile = NULL;
				if ("hdmv_pgs_subtitle" == $codecName) {
					// convert to dvd
					//$dvdFile = getEnvWithDefault("TMP_DIR", "/tmp")."/".$filename.'-'.$subtitle->index.'.sub';
					$pgsFile = $filename.'-'.$index.'.sup';
					if (!file_exists($pgsFile)) {
						$command = 'ffmpeg -y -i "'.$filename.'" -map 0:'.$index.' -c copy '.$pgsFile;
						printf("Extract pgs command: %s\n", $command);
						exec($command, $out, $return);
						if ($return != 0) {
							printf("pgs extract failed: %s\n", $return);
							exit($return);
						}
					}
					$dvdFile = $filename.'-'.$index;
					if (!file_exists($dvdFile.".sub")) {
						$command = "java -jar /home/ripvideo/BDSup2Sub.jar -o ".$dvdFile.".sub ".$pgsFile;
						printf("Convert pgs to dvd command: %s", $command);
						exec($command, $out, $return);
						if ($return != 0) {
						    printf("sub convertion failed: %s\n", $return);
						    exit($return);
						}
					}
				} else if ("vobsub" == $codeName) {
					// extract vobsub
					$dvdFile = $filename.'-'.$index;
					if (!file_exists($dvdFile.".sub")) {
						$command = 'ffmpeg -y -i "'.$filename.'" -map 0:'.$index.' -c copy '.$dvdFile.'.sub';
						printf("Extract vobsub command: %s", $command);
						exec($command, $out, $return);
						if ($return != 0) {
						    printf("vobsub extraction failed: %s\n", $return);
						    exit($return);
						}
					}
				}
										
				// convert to srt
				if (NULL != $dvdFile) {
					if (!file_exists($dvdFile.".srt")) {
	                                        $command = 'vobsub2srt "'.$dvdFile;
        	                                printf("Convert vobsub to srt command: %s", $command);
                	                        exec($command, $out, $return);
                        	                if ($return != 0) {
                                	            printf("vobsub to srt conversion failed: %s\n", $return);
                                        	    exit($return);
	                                        }
					}
					$oNewRequest = new Request($dvdFile.".srt");
					$oNewRequest->subtitleTrack = 0;
					$oNewRequest->subtitleFormat = $oRequest->subtitleFormat;
					$arrAdditionalRequests[] = $oNewRequest;
					$oRequest->oInputFile->removeSubtitleStream($index);
                                }
			}
		}
		return $arrAdditionalRequests;
	}
}

?>

