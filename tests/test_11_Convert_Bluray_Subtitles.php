<?php
include_once "common.php";

getFile("bluray.mkv", "https://".$sampleDomain."/samples/Bluray_Sample.mkv");

test_ffmpeg(array("APPLY_POSTFIX"=>"false", "INPUT"=>"bluray.mkv", "TITLE"=>"Test Convert Bluray Subtitle", "VIDEO_TRACKS"=>-1, "AUDIO_TRACKS"=>-1, "YEAR"=>2019), $output, $return);

test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Convert Bluray Subtitle (2019).mkv");
$probe = json_decode($probe, true);
$testOutput = array($output, $probe);

test("Stream 0", "subtitle", $probe["streams"][0]["codec_type"], $testOutput);
test("Stream 0 codec", getEnv("BUILD_SUBTITLE_CONVERT") == "false" ? "hdmv_pgs_subtitle" : "ass", $probe["streams"][0]["codec_name"], $testOutput);
test("Stream 0 language", "eng", $probe["streams"][0]["tags"]["language"], $testOutput);
test("Stream 1 exists", FALSE, array_key_exists(1, $probe["streams"]), $testOutput);
test("Metadata Title", "Test Convert Bluray Subtitle", $probe["format"]["tags"]["title"], $testOutput);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $testOutput);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $testOutput);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $testOutput);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $testOutput);

?>

