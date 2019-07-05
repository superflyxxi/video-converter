#!/bin/php
<?php

include_once "InputFile.php";
include_once "OutputFile.php";

function getEnvWithDefault($env, $default) {
	if (getEnv($env)) {
		return getEnv($env);
	} else {
		return $default;
	}
}

$title = getEnv("TITLE");
if (!$title) {
	print_r("Missing TITLE variable\n");
	exit(1);
}

$oOutput = new OutputFile();
$oOutput->title = $title;
$oOutput->subtitle = getEnv("SUBTITLE");
$oOutput->season = getEnv("SEASON");
$oOutput->year = getEnv("YEAR");

$metadata = "";
$outputFile = getEnvWithDefault("OUTPUT_DIR", "/data")."/".$output.".ffmpeg.mkv";

$input = "/data/".getEnvWithDefault("INPUT", ".");
if (is_dir($input) || substr($input, -strlen($input)) === ".iso") {
	print_r("Using bluray directory\n");
	$input = "bluray:".$input;
} else {
	print_r("Using filename\n");
}

$playlistArgs = (getEnv("PLAYLIST") ? "-playlist ".getEnv("PLAYLIST") : "");

$subtitleTrackArgs = "-map 0:".getEnvWithDefault("SUBTITLE_TRACK", "s?")
	." -c:s ".getEnvWithDefault("SUBTITLE_FORMAT", "ass");

$audioFormat = getEnvWithDefault("AUDIO_FORMAT", "aac");
$audioTrackArgs = "-map 0:".getEnvWithDefault("AUDIO_TRACK", "a")." -c:a ".$audioFormat;
if ("copy" != $audioFormat) {
	foreach ( explode(" ", getEnvWithDefault("AUDIO_CHANNEL_MAPPING_TRACKS", "1")) as $track ){
		$audioTrackArgs .= " -filter:".$track." channelmap=channel_layout=".getEnvWithDefault("AUDIO_CHANNEL_LAYOUT", "5.1");
	}
	$audioTrackArgs .= " -q:a ".getEnvWithDefault("AUDIO_QUALITY", "2");
}

$hwaccel = is_dir("/dev/dri"); // hwaccel is true if the device is mapped
$ffmpegHwaccelArgs = "";
$deinterlaceArgs = "";
if ($hwaccel) {
	$ffmpegHwaccelArgs = "-hwaccel vaapi -hwaccel_output_format vaapi -hwaccel_device /dev/dri/renderD128";
	if ("true" == getEnvWithDefault("DEINTERLACE", "false")) {
		$deinterlaceArgs = "-vf deinterlace_vaapi=rate=field:auto=1";
	}
}

$videoFromat = getEnvWithDefault("VIDEO_FORMAT", "notcopy");
$videoTrackArgs = "-map 0:".getEnvWithDefault("VIDEO_TRACK", "v");
if ( "copy" == $videoFromat ) {
        $videoTrackArgs .= " -c:v copy";
} else if ($hwaccel) {
	$videoTrackArgs .= " -c:v hevc_vaapi -qp 20 -level:v 41";
} else if ("true" == getEnvWithDefault("HDR", "false")) {
	$videoTrackArgs .= " -c:v libx265 -crf 20 -level:v 51 -pix_fmt yuv420p10le -color_primaries 9 -color_trc 16 -colorspace 9 -color_range 1 -profile:v main10";
} else {
	$videoTrackArgs .= " -c:v libx265 -crf 20 -level:v 41";
}

$probeCommand = "ffprobe -v quiet -print_format json -show_format -show_streams \"".$input."\"";

print_r("Executing probe: ");
print_r($probeCommand);
print_r("\n");

exec($probeCommand, $systemOut, $returnValue);
$oInputFile = new InputFile($ffprobeJson = json_decode(implode($systemOut), true));
print_r($systemOut);
print_r("Input file object: ");
print_r($oInputFile);
print_r("\n");

$finalCommand = "ffmpeg "
	." ".("true" == getEnvWithDefault("OVERWRITE_FILE", "true") ? "-y" : "")
	." ".$ffmpegHwaccelArgs
	." ".$playlistArgs
	.' -i "'.$input.'"'
	." ".$videoTrackArgs
	." ".$deinterlaceArgs
	." ".$audioTrackArgs
	." ".$subtitleTrackArgs
	." ".$metadata
	." ".getEnvWithDefault("OTHER_METADATA", " ")
	.' -f matroska "'.$outputFile.'"';

print_r("Going to execute: ");
print_r($finalCommand);
print_r("\n");

exec($finalCommand, $systemOut, $returnValue);

print_r("\nReturning ");
print_r($returnValue);
print_r("\n");

exit($returnValue);

/*if [[ "${DOCKER_DAEMON}" != "y" && "${NORMALIZE:-n}" == "y" ]]; then
	# Save an Array of Values from output for only measured values
	NORMALIZE_SH=./normalizeAudio.sh
	INPUT="${OUTPUT_FILE}" AUDIO_CHANNEL_LAYOUT=${AUDIO_CHANNEL_LAYOUT} AUDIO_FORMAT=${AUDIO_FORMAT} \
		AUDIO_QUALITY=${AUDIO_QUALITY} ${NORMALIZE_SH}
fi;
*/
?>

