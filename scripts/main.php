#!/bin/php
<?php

include_once "Request.php";
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

$oRequest = new Request("/data/".getEnvWithDefault("INPUT", "."));
$oRequest->playlist = getEnv("PLAYLIST");
$oRequest->subtitleTrack = getEnvWithDefault("SUBTITLE_TRACK", "s?");
$oRequest->subtitleFormat = getEnvWithDefault("SUBTITLE_FORMAT", "ass");

$oRequest->audioTrack = getEnvWithDefault("AUDIO_TRACK", "a");
$oRequest->audioFormat = getEnvWithDefault("AUDIO_FORMAT", "aac");
$oRequest->audioQuality = getEnvWithDefault("AUDIO_QUALITY", "2");
$oRequest->audioChannelMappingTracks = explode(" ", getEnvWithDefault("AUDIO_CHANNEL_MAPPING_TRACKS", "1"));

$oRequest->deinterlace = ("true" == getEnvWithDefault("DEINTERLACE", "false"));

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

