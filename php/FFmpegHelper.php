<?php

include_once "Request.php";
include_once "functions.php";
include_once "OutputFile.php";
include_once "Logger.php";

class FFmpegHelper {

    public static function probe($inputFile) {
	$command = 'ffprobe -v quiet -print_format json -show_format -show_streams "'.$inputFile->getPrefix().$inputFile->getFileName().'"';
	Logger::verbose("Executing ffprobe: {}", array($command));
	exec($command, $out, $ret);
	if ($ret > 0) {
		Logger::error("Failed to execute ffprobe; returned {}", array($ret));
		exit ($ret);
	}
	return $out;
    }

    public static function execute($listRequests, $outputFile, $exit = TRUE) {
	$command = self::generate($listRequests, $outputFile);
	Logger::verbose("Executing ffmpeg: {}", array($command));
	passthru($command." 2>&1", $ret);
	if ($exit && $ret > 0) {
		Logger::error("Failed to execute ffmpeg with return code {}", array($ret));
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
            $finalCommand .= ' -i "'.$tmpRequest->oInputFile->getPrefix().$tmpRequest->oInputFile->getFileName().'" ';
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
			$args .= " -metadata:s:v:".$videoTrack." language=".$stream->language;
			$videoTrack++;
		}
		return $args;
	}

	private static function generateAudioArgs($fileno, $request, &$audioTrack) {
		$args = " ";
		foreach ($request->oInputFile->getAudioStreams() as $index => $stream) {
			$args .= " -map ".$fileno.":".$index;
			if ("copy" != $request->audioFormat) {
				if (array_key_exists($index, $request->audioChannelMapping)) {
					$channelLayout = $request->audioChannelMapping[$index];
				} else {
					$channelLayout = $stream->channel_layout;
				}
				if (NULL != $channelLayout) {
					$channelLayout = preg_replace("/\(.+\)/", '', $channelLayout);
					$args .= " -filter:a:".$audioTrack.' channelmap=channel_layout='.$channelLayout;
				}
				$args .= " -c:a:".$audioTrack." ".$request->audioFormat;
				$args .= " -q:a:".$audioTrack." ".$request->audioQuality;
			} else {
				// specify copy
				$args .= " -c:a:".$audioTrack." copy";
			}
			$args .= " -metadata:s:a:".$audioTrack." language=".$stream->language;
			$audioTrack++;
		}
		return $args;
	}

	private static function generateSubtitleArgs($fileno, $request, &$subtitleTrack) {
		$args = " ";
		foreach ($request->oInputFile->getSubtitleStreams() as $index => $stream) {
			$args .= " -map ".$fileno.":".$index." -c:s:".$subtitleTrack." ".$request->subtitleFormat;
			$args .= " -metadata:s:s:".$subtitleTrack." language=".$stream->language;
			$subtitleTrack++;
		}
		return $args;
	}

}

?>

