#!/bin/php
<?php

include_once "Request.php";
include_once "OutputFile.php";
include_once "functions.php";
include_once "SubtitleConvert.php";
include_once "NormalizeAudio.php";
include_once "FFmpegHelper.php";

if (!getEnv("TITLE")) {
	print_r("Missing TITLE variable\n");
	exit(1);
}

$oOutput = new OutputFile();
$oOutput->title = getEnv("TITLE");
$oOutput->subtitle = getEnv("SUBTITLE");
$oOutput->season = getEnv("SEASON");
$oOutput->episode = getEnv("EPISODE");
$oOutput->year = getEnv("YEAR");

$oRequest = new Request("/data/".getEnvWithDefault("INPUT", "."));
$allRequests[] = $oRequest;
$allRequests = array_merge($allRequets, SubtitleConvert::convert($oRequest));
$allRequests = array_merge($allRequets, NormalizeAudio::normalize($oRequest));

$finalCommand = FFmpegHelper::generate($allRequests, $oOutput);

printf("ffmpeg command: %s\n", $finalCommand);
exec($finalCommand, $systemOut, $returnValue);

printf("Completed with %s return value.\n\n", $returnValue);

exit($returnValue);

?>

