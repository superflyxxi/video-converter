<?php

include_once "Request.php";
include_once "functions.php";
include_once "OutputFile.php";

class FFmpegHelper {
	
	public static function generateHardwareAccelArgs() {
		return " ".(file_exists("/dev/dri") ? "-hwaccel vaapi -hwaccel_output_format vaapi -hwaccel_device /dev/dri/renderD128" : " ");
	}
	
	public static function generateMetadataArgs($outputFile) {
		return " ".(NULL != $outputFile->title ? '-metadata "title='.$outputFile->title.'"' : " ")
			." ".(NULL != $outputFile->subtitle ? '-metadata "subtitle='.$outputFile->subtitle.'"' : " ")
			." ".(NULL != $outputFile->year ? '-metadata "year='.$outputFile->year.'"' : " ")
			." ".(NULL != $outputFile->season ? '-metadata "season='.$outputFile->season.'"' : " ")
			." ".(NULL != $outputFile->episode ? '-metadata "episode='.$outputFile->episode.'"' : " ")
			." ".getEnvWithDefault("OTHER_METADATA", " ");
	}

	public static function generateArgs($fileno, $request) {
		$args = " ".self::generateVideoArgs($fileno, $request);
		$args .= " ".self::generateAudioArgs($fileno, $request);
		$args .= " ".self::generateSubtitleArgs($fileno, $request);
		return $args;
	}

	private static function generateVideoArgs($fileno, $request) {
		$args = " ";
		foreach ($request->oInputFile->getVideoStreams() as $index => $stream) {
			$args .= " -map ".$fileno.":".$index;
			if ("copy" == $request->videoFormat) {
				$args .= " -c:v copy";
			} else if ($request->isHDR()) {
				$args .= " -c:v libx265 -crf 20 -level:v 51 -pix_fmt yuv420p10le -color_primaries 9 -color_trc 16 -colorspace 9 -color_range 1 -profile:v main10";
			} else if ($request->isHwaccel()) {
				$args .= " -c:v hevc_vaapi -qp 20 -level:v 41";
			} else {
				$args .= " -c:v libx265 -crf 20 -level:v 41";
			}	
		}
		return $args;
	}

	private static function generateAudioArgs($fileno, $request) {
		$args = " ";
		foreach ($request->oInputFile->getAudioStreams() as $index => $stream) {
			$args .= " -map ".$fileno.":".$index." -c:a ".$request->audioFormat;
			if ("copy" != $request->audioFormat) {
				$args .= " -q:a ".$request->audioQuality;
			}
		}
		return $args;
	}

	private static function generateSubtitleArgs($fileno, $request) {
		$args = " ";
		foreach ($request->oInputFile->getSubtitleStreams() as $index => $stream) {
			$args .= " -map ".$fileno.":".$index." -c:a ".$request->subtitleFormat;
		}
		return $args;
	}


}

?>

