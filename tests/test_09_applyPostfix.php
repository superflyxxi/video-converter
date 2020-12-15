<?php
include_once "common.php";

getFile("dvd.mkv", "https://".$sampleDomain."/samples/DVD_Sample.mkv");

test_ffmpeg(array("APPLY_POSTFIX"=>"false", "INPUT"=>"dvd.mkv", "TITLE"=>"Test Not Applying Postfix", "YEAR"=>2019, "VIDEO_FORMAT"=>"copy", "AUDIO_TRACKS"=>-1, "SUBTITLE_TRACKS"=>-1), $output, $return);
test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Not Applying Postfix (2019).mkv");
$probe = json_decode($probe, true);

$testOutput = array($output, $probe);

test("Stream 0", "video", $probe["streams"][0]["codec_type"], $testOutput);
test("Stream 0 codec", "mpeg2video", $probe["streams"][0]["codec_name"], $testOutput);
test("Stream 1 doesn't exist", FALSE, array_key_exists(1, $probe["streams"]), $testOutput);
test("Metadata Title", "Test Not Applying Postfix", $probe["format"]["tags"]["title"], $testOutput);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $testOutput);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $testOutput);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $testOutput);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $testOutput);

?>

