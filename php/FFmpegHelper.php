<?php

include_once "Request.php";
include_once "functions.php";

class FFmpegHelper {
	
	public static function generateMainArgs($outputFile) {
		return "ffmpeg"
			." ".(getEnvWithDefault("OVERWRITE", "true") == "true" ? "-y" : " ")
			." ".(file_exists("/dev/dri") ? "-hwaccel vaapi -hwaccel_output_format vaapi -hwaccel_device /dev/dri/renderD128" : " ")
			." ".(NULL != $outputFile->title ? '-metadata "title='.$outputFile->title.'"' : " ")
			." ".(NULL != $outputFile->subtitle ? '-metadata "subtitle='.$outputFile->subtitle.'"' : " ")
			." ".(NULL != $outputFile->year ? '-metadata "year='.$outputFile->year.'"' : " ")
			." ".(NULL != $outputFile->season ? '-metadata "season='.$outputFile->season.'"' : " ")
			." ".(NULL != $outputFile->episode ? '-metadata "episode='.$outputFile->episode.'"' : " ")
			." ".getEnvWithDefault("OTHER_METADATA", " ");
			
			
	}

	public static function generateArgs($fileno, $request) {
		$args = self::generateVideoArgs($fileno, $request);
		$args .= " ".self::generateAudioArgs($fileno, $request);
		$args .= " ".self::generateSubtitleArgs($fileno, $request);
		return $args;
	}

	private static function generateVideoArgs($fileno, $request) {
		return " ";
	}

	private static function generateAudioArgs($fileno, $request) {
		return " ";
	}

	private static function generateSubtitleArgs($fileno, $request) {
		return " ";
	}


}

?>

