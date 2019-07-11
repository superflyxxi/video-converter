<?php

include_once "Request.php";
include_once "InputFile.php";
include_once "functions.php";

class SubtitleConvert {

	public static function convert($oRequest) {
		$dir = getEnvWithDefault("TMP_DIR", "/tmp");
		$arrAdditionalRequests = array();
		if ($oRequest->subtitleFormat != "copy") {
			$filename = $oRequest->oInputFile->getFileName();
			foreach ($oRequest->oInputFile->getSubtitleStreams() as $index => $subtitle) {
				$codecName = $subtitle->codec_name;
				$dvdFile = NULL;
				if ("hdmv_pgs_subtitle" == $codecName) {
					// convert to dvd
					if ($oRequest->oInputFile->getPrefix() != NULL) {
						$dvdFile = $dir."/".realpath($filename)."/dir-".$index;
					} else {
						$dvdFile = $dir."/".$filename.'-'.$index;
					}
					$pgsFile = new OutputFile($dvdFile.'.sup');
					if (!file_exists($pgsFile->getFileName())) {
						$pgsRequest = new Request($filename);
						$pgsRequest->subtitleTrack = $index;
						$pgsRequest->subtitleFormat = "copy";
						$pgsRequest->prepareStreams();
						printf("Generating PGS sup file for index %s of file '%s'.\n", $index, $filename);
						if (FFmpegHelper::execute(array($pgsRequest), $pgsFile) > 0) {
							printf("Conversion failed... Skipping this stream.\n");
							continue;
						}
					}

					if (!file_exists($dvdFile.'.sub')) {
						$command = 'java -jar /home/ripvideo/BDSup2Sub.jar -o "'.$dvdFile.'.sub" "'.$pgsFile->getFileName().'"';
						printf("Convert pgs to dvd command: %s\n", $command);
						exec($command, $out, $return);
						if ($return != 0) {
						    printf("sub convertion failed: %s\nContinuing with next subtitle.\n", $return);
						    continue;
						}
					}
				} else if ("vobsub" == $codeName) {
					// extract vobsub
					if ($oRequest->oInputFile->getPrefix() != NULL) {
						$dvdFile = $dir."/".realpath($filename)."/dir-".$index;
					} else {
						$dvdFile = $dir."/".$filename.'-'.$index;
					}
					$dvdOutputFile = new OutputFile($dvdFile.".sub");
					if (!file_exists($dvdOutputFile->getFileName())) {
						$dvdRequest = new Request($filename);
						$dvdRequest->subtitleTrack = $index;
						$dvdRequest->subtitleFormat = "copy";
						$dvdRequest->prepareStreams();
						printf("Generating DVD sub file for index %s of file %s.\n", $index, $filename);
						if (FFmpegHelper::execute(array($dvdRequest), $dvdOutputFile) > 0) {
							printf("Conversion failed... Skipping this stream.\n");
							continue;
						}
					}
				}
										
				// convert to srt
				if (NULL != $dvdFile) {
					if (!file_exists($dvdFile.".srt")) {
	                                        $command = 'vobsub2srt "'.$dvdFile.'"';
        	                                printf("Convert DVD sub using command: %s\n", $command);
                	                        exec($command, $out, $return);
                        	                if ($return != 0) {
                                	            printf("vobsub to srt conversion failed: %s\nContinuing with next stream.\n", $return);
                                        	    continue;
	                                        }
					}
					$oNewRequest = new Request($dvdFile.".srt");
					$oNewRequest->subtitleTrack = 0;
					$oNewRequest->subtitleFormat = $oRequest->subtitleFormat;
					$oNewRequest->prepareStreams();
					$oNewRequest->oInputFile->getSubtitleStreams()[0]->language = $subtitle->language;
					printf("Using %s language for final stream.\n", $subtitle->language);
					$arrAdditionalRequests[] = $oNewRequest;
					$oRequest->oInputFile->removeSubtitleStream($index);
                                }
			}
			// if for some reason some couldn't be converted, copy the ones in the main input file
			$oRequest->subtitleFormat="copy";
		}
		return $arrAdditionalRequests;
	}
}

?>

