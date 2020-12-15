<?php
include_once "common.php";

getFile("dvd.mkv", "https://".$sampleDomain."/samples/DVD_Sample.mkv");

test_ffmpeg(array("APPLY_POSTFIX"=>"false", "INPUT"=>"dvd.mkv", "TITLE"=>"Test Convert DVD Subtitle", "VIDEO_TRACKS"=>-1, "AUDIO_TRACKS"=>-1, "SUBTITLE_FORMAT"=>"srt", "YEAR"=>2019), $output, $return);

test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Convert DVD Subtitle (2019).mkv");
$probe = json_decode($probe, true);

$testOutput = array($output, $probe);

test("Stream 0", "subtitle", $probe["streams"][0]["codec_type"], $testOutput);
test("Stream 0 codec", getEnv("BUILD_SUBTITLE_CONVERT") == "false" ? "dvd_subtitle" : "subrip", $probe["streams"][0]["codec_name"], $testOutput);
test("Stream 0 language", "eng", $probe["streams"][0]["tags"]["language"], $testOutput);
test("Stream 1 exists", FALSE, array_key_exists(1, $probe["streams"]), $testOutput);
test("Metadata Title", "Test Convert DVD Subtitle", $probe["format"]["tags"]["title"], $testOutput);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $testOutput);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $testOutput);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $testOutput);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $testOutput);

?>

