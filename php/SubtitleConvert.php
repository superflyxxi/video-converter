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
					$pgsFile = new OutputFile($dir."/".$filename.'-'.$index.'.sup');
					$pgsRequest = new Request($filename);
					$pgsRequest->subtitleTrack = $index;
					$pgsRequest->subtitleFormat = "copy";
					$pgsRequest->prepareStreams();
					printf("Generating PGS sup file for index %s of file %s.\n", $index, $filename);
					FFmpegHelper::execute(array($pgsRequest), $pgsFile);

					$dvdFile = $dir."/".$filename.'-'.$index;
					$command = 'java -jar /home/ripvideo/BDSup2Sub.jar -o "'.$dvdFile.'.sub" "'.$pgsFile->getFileName().'"';
					printf("Convert pgs to dvd command: %s\n", $command);
					exec($command, $out, $return);
					if ($return != 0) {
					    printf("sub convertion failed: %s\n", $return);
					    exit($return);
					}
				} else if ("vobsub" == $codeName) {
					// extract vobsub
					$dvdFile = $dir."/".$filename.'-'.$index;
					$dvdOutputFile = new OutputFile($dvdFile.".sub");
					$dvdRequest = new Request($filename);
					$dvdRequest->subtitleTrack = $index;
					$dvdRequest->subtitleFormat = "copy";
					$dvdRequest->prepareStreams();
					printf("Generating DVD sub file for index %s of file %s.\n", $index, $filename);
					FFmpegHelper::execute(array($dvdRequest), $dvdOutputFile);
				}
										
				// convert to srt
				if (NULL != $dvdFile) {
					if (!file_exists($dvdFile.".srt")) {
	                                        $command = 'vobsub2srt "'.$dvdFile.'"';
        	                                printf("Convert DVD sub using command: %s\n", $command);
                	                        exec($command, $out, $return);
                        	                if ($return != 0) {
                                	            printf("vobsub to srt conversion failed: %s\n", $return);
                                        	    exit($return);
	                                        }
					}
					$oNewRequest = new Request($dvdFile.".srt");
					$oNewRequest->subtitleTrack = 0;
					$oNewRequest->subtitleFormat = $oRequest->subtitleFormat;
					$oNewRequest->prepareStreams();
					$arrAdditionalRequests[] = $oNewRequest;
					$oRequest->oInputFile->removeSubtitleStream($index);
                                }
			}
		}
		return $arrAdditionalRequests;
	}
}

?>

