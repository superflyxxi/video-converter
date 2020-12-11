<?php
include_once "common.php";

getFile("dvd.mkv", "https://".$sampleDomain."/samples/DVD_Sample.mkv");

test_ffmpeg(array("INPUT"=>"dvd.mkv", "TITLE"=>"Test Normalize Track 1", "YEAR"=>2019, "NORMALIZE_AUDIO_TRACKS"=>1, "VIDEO_TRACKS"=>-1, "SUBTITLE_TRACKS"=>-1), $output, $return);
test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Normalize Track 1 (2019).dvd.mkv.mkv");
$probe = json_decode($probe, true);

$testOutput = array($output, $probe);

test("Stream 0", "audio", $probe["streams"][0]["codec_type"], $testOutput);
test("Stream 0 codec", "aac", $probe["streams"][0]["codec_name"], $testOutput);
test("Stream 0 channels", 6, $probe["streams"][0]["channels"], $testOutput);
test("Stream 0 channel_layout", "5.1", $probe["streams"][0]["channel_layout"], $testOutput);
test("Stream 1", "audio", $probe["streams"][1]["codec_type"], $testOutput);
test("Stream 1 codec", "aac", $probe["streams"][1]["codec_name"], $testOutput);
test("Stream 1 channel_layout", "5.1", $probe["streams"][1]["channel_layout"], $testOutput);
test("Stream 1 channels", 6, $probe["streams"][1]["channels"], $testOutput);
test("Stream 1 title", "Normalized eng 5.1", $probe["streams"][1]["tags"]["title"], $testOutput);
test("Stream 2 doesn't exist", FALSE, array_key_exists(2, $probe["streams"]), $testOutput);
test("Metadata Title", "Test Normalize Track 1", $probe["format"]["tags"]["title"], $testOutput);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $testOutput);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $testOutput);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $testOutput);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $testOutput);

?>

