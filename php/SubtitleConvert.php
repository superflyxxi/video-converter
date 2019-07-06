<?php

include_once "Request.php";
include_once "InputFile.php";

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
					$dvdFile = $filename.'-'.$subtitle->index.'.sub';
					$dvdIndex = 0;
					$command = 'ffmpeg -i "'
						.$filename
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

