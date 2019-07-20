#!/bin/php
<?php
include_once "Logger.php";
include_once "Request.php";
include_once "OutputFile.php";
include_once "functions.php";
include_once "SubtitleConvert.php";
include_once "NormalizeAudio.php";
include_once "FFmpegHelper.php";

if (! getEnv("TITLE")) {
    Logger::error("Missing TITLE variable");
    exit(1);
}
Logger::info("Starting conversion");
$oOutput = new OutputFile();
$oOutput->title = getEnv("TITLE");
$oOutput->subtitle = getEnv("SUBTITLE");
$oOutput->season = getEnv("SEASON");
$oOutput->episode = getEnv("EPISODE");
$oOutput->year = getEnv("YEAR");

$oRequest = Request::newInstanceFromEnv("/data/" . getEnvWithDefault("INPUT", "."));
$allRequests[] = $oRequest;
$allRequests = array_merge($allRequests, NormalizeAudio::normalize($oRequest));
$allRequests = array_merge($allRequests, SubtitleConvert::convert($oRequest));

$returnValue = FFmpegHelper::execute($allRequests, $oOutput);

Logger::info("Completed conversion with {} as a return value.", array(
    $returnValue
));
exit($returnValue);

?>
