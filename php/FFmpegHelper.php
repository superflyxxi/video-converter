<?php

include_once "Request.php";
include_once "functions.php";
include_once "OutputFile.php";

class FFmpegHelper {

    public static function execute($listRequests, $outputFile) {
	$command = self::generate($listRequests, $outputFile);
	printf("Executing ffmpeg: %s\n", $command);
	exec($command." 2>&1", $out, $ret);
	if ($ret > 0) {
		print_r($out);
		printf("Failed to execute ffmpeg with return code %s\n", $ret);
		exit($ret);
	}
	return $ret;
    }
	
    public static function generate($listRequests, $outputFile) {
        $finalCommand = "ffmpeg ";
        if (getEnvWithDefault("OVERWRITE_FILE", "true") == "true") {
            $finalCommand .= "-y ";
        }
        $finalCommand .= self::generateHardwareAccelArgs();
        
        // generate input args
        foreach ($listRequests as $tmpRequest) {
            $finalCommand .= ' -i "'.$tmpRequest->oInputFile->getFileName().'" ';
        }
        
        $fileno = 0;
	$videoTrack = 0;
	$audioTrack = 0;
	$subtitleTrack = 0;
        foreach ($listRequests as $tmpRequest) {
            $finalCommand .= " ".self::generateVideoArgs($fileno, $tmpRequest, $videoTrack);
            $finalCommand .= " ".self::generateAudioArgs($fileno, $tmpRequest, $audioTrack);
            $finalCommand .= " ".self::generateSubtitleArgs($fileno, $tmpRequest, $subtitleTrack);
            $finalCommand .= " ".self::generateMetadataArgs($fileno, $tmpRequest);
            $fileno++;
        }
        
        $finalCommand .= self::generateGlobalMetadataArgs($outputFile);
        $finalCommand .= ' "'.$outputFile->getFileName().'"';
        
        return $finalCommand;
    }
    
    private static function generateHardwareAccelArgs() {
		return " ".(file_exists("/dev/dri") ? "-hwaccel vaapi -hwaccel_output_format vaapi -hwaccel_device /dev/dri/renderD128" : " ");
	}
	
	private static function generateGlobalMetadataArgs($outputFile) {
		return " ".(NULL != $outputFile->title ? '-metadata "title='.$outputFile->title.'"' : " ")
			." ".(NULL != $outputFile->subtitle ? '-metadata "subtitle='.$outputFile->subtitle.'"' : " ")
			." ".(NULL != $outputFile->year ? '-metadata "year='.$outputFile->year.'"' : " ")
			." ".(NULL != $outputFile->season ? '-metadata "season='.$outputFile->season.'"' : " ")
			." ".(NULL != $outputFile->episode ? '-metadata "episode='.$outputFile->episode.'"' : " ")
			." ".getEnvWithDefault("OTHER_METADATA", " ");
	}
	
	private static function generateMetadataArgs($fileno, $request) {
		return " ";
	}

	private static function generateVideoArgs($fileno, $request, &$videoTrack) {
		$args = " ";
		foreach ($request->oInputFile->getVideoStreams() as $index => $stream) {
			$args .= " -map ".$fileno.":".$index;
			if ("copy" == $request->videoFormat) {
				$args .= " -c:v:".$videoTrack." copy";
			} else if ($request->isHDR()) {
				$args .= " -c:v:".$videoTrack." libx265 -crf 20 -level:v 51 -pix_fmt yuv420p10le -color_primaries 9 -color_trc 16 -colorspace 9 -color_range 1 -profile:v main10";
			} else if ($request->isHwaccel()) {
				$args .= " -c:v:".$videoTrack." hevc_vaapi -qp 20 -level:v 41";
			} else {
				$args .= " -c:v:".$videoTrack." libx265 -crf 20 -level:v 41";
			}
			$videoTrack++;
		}
		return $args;
	}

	private static function generateAudioArgs($fileno, $request, &$audioTrack) {
		$args = " ";
		foreach ($request->oInputFile->getAudioStreams() as $index => $stream) {
			$args .= " -map ".$fileno.":".$index;
			$args .= " -c:a:".$audioTrack." ".$request->audioFormat;
			if ("copy" != $request->audioFormat) {
				$args .= " -q:a:".$audioTrack." ".$request->audioQuality;
				if (array_key_exists($index, $request->audioChannelMapping)) {
					$args .= " -filter:a:".$audioTrack." channelmap=channel_layout=".$request->audioChannelMapping[$index];
				}
			}
			$audioTrack++;
		}
		return $args;
	}

	private static function generateSubtitleArgs($fileno, $request, &$subtitleTrack) {
		$args = " ";
		foreach ($request->oInputFile->getSubtitleStreams() as $index => $stream) {
			$args .= " -map ".$fileno.":".$index." -c:s:".$subtitleTrack." ".$request->subtitleFormat;
			$subtitleTrack++;
		}
		return $args;
	}

}

?>

