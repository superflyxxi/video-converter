<?php

include_once "Request.php";
include_once "InputFile.php";

class SubtitleConvert {
	public static function convert($oRequest) {
		if ($oRequest->subtitleFormat != "copy") {
			foreach ($oRequest->getSubtitleStreams() as $subtitle) {
				$codecName = $subtitle->codecName;
				$dvdFile = $subtitle->getFileName();
				$dvdIndex = $subtitle->index;
				if ("hdmv_pgs_subtitle" == $codeName) {
					// convert to dvd
					$dvdFile = $oRequest->getFileName().'-'.$subtitle->index.'.sub';
					$dvdIndex = 0;
					$command = 'ffmpeg -i "'
						.$oRequest->getFileName()
						.'" -map 0:'
						.$subtitle->index
						.' -c copy '.$dvdFile;
					exec($command, $out, $return);
					if (!$return) {
						print_r("pgs extract failed");
						exit($return);
					}
				}
					
				// convert to srt
			}
		}
	}
}

?>

