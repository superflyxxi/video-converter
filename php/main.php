#!/bin/php
<?php

include_once "Request.php";
include_once "OutputFile.php";
include_once "functions.php";
include_once "SubtitleConvert.php";
include_once "FFmpegHelper.php";

if (!getEnv("TITLE")) {
	print_r("Missing TITLE variable\n");
	exit(1);
}

$oOutput = new OutputFile();
$oOutput->title = getEnv("TITLE");
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

$oRequest->videoTrack = getEnvWithDefault("VIDEO_TRACK", "v");
$oRequest->videoFormat = getEnvWithDefault("VIDEO_FORMAT", "notcopy");

$oRequest->prepareStreams();
$otherRequests = SubtitleConvert::convert($oRequest);

printf("Original Request\n");
print_r($oRequest);
printf("\n\nNew Additional Requests\n");
print_r($otherRequests);

$allRequests = array_merge(array($oRequest), $arrRequests);

$finalCommand = "ffmpeg ";
if (getEnvWithDefault("OVERWRITE_FILE", "true") == "true") {
	$finalCommand .= "-y ";
}
$finalCommand .= FFmpegHelper::generateHardwareAccelArgs();

// generate input args
foreach ($allRequests as $tmpRequest) {
	$finalCommand .= ' -i "'.$tmpRequest->oInputFile->getFileName().'" ';
}

$fileno = 0;
foreach ($allRequests as $tmpRequest) {
	$finalCommand .= " ".FFmpegHelper::generateArgs($fileno++, $tmpRequest);
}

$finalCommand .= FFmpegHelper::generateMetadataArgs($oOutput);
$finalCommand .= ' -f matroska "'.$oOutput->getOutputFile().'.mkv"';

printf("ffmpeg command: %s\n", $finalCommand);
exec($finalCommand, $systemOut, $returnValue);

printf("Completed with %s return value.", $returnValue);

exit($returnValue);

?>

