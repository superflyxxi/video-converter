<?php

include_once "Request.php";
include_once "InputFile.php";
include_once "functions.php";

class SubtitleConvert {
	public static function convert($oRequest) {
		if ($oRequest->subtitleFormat != "copy") {
			$filename = $oRequest->oInputFile->getFileName();
			foreach ($oRequest->oInputFile->getSubtitleStreams() as $subtitle) {
				$codecName = $subtitle->codec_name;
				$dvdFile = $filename;
				$dvdIndex = $subtitle->index;
				if ("hdmv_pgs_subtitle" == $codecName) {
					// convert to dvd
					//$dvdFile = getEnvWithDefault("TMP_DIR", "/tmp")."/".$filename.'-'.$subtitle->index.'.sub';
					$pgsFile = $filename.'-'.$subtitle->index.'.sup';
					$command = 'ffmpeg -y -i "'
						.$filename
						.'" -map 0:'
						.$subtitle->index
						.' -c copy '.$pgsFile;
					printf("Extract pgs command: %s\n", $command);
					exec($command, $out, $return);
					if ($return != 0) {
						printf("pgs extract failed: %s\n", $return);
						exit($return);
					}
					$dvdIndex = 0;
					$dvdFile = $filename.'-'.$subtitle->index.'.sub';
					$command = "java -jar /home/ripvideo/BDSup2Sub.jar -o ".$dvdFile." ".$pgsFile;
					printf("Convert pgs to dvd command: %s", $command);
					exec($command, $out, $return);
					if ($return != 0) {
					    printf("sub convertion failed: %s\n", $return);
					    exit($return);
					}
					
				}
					
				// convert to srt
			}
		}
	}
}

?>

