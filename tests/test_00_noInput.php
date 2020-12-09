<?php
/*
 * Tests when no input is given. It should product results for the file as expected.
 */
include_once "common.php";

getFile("dvd.mkv", "https://".$sampleDomain."/samples/DVD_Sample.mkv");
test_ffmpeg(array("TITLE"=>"Test No Input", "YEAR"=>2019, "AUDIO_FORMAT"=>"copy", "VIDEO_FORMAT"=>"copy", "SUBTITLE_FORMAT"=>"copy"), $output, $return);

test("ffmpeg code", 0, $return, $output);

$probe = json_decode(probe("/data/Test No Input (2019).dvd.mkv.mkv", true));

$testOutput = array(
    $output,
    $probe
);
test("Stream 0", "video", $probe["streams"][0]["codec_type"], $testOutput);
test("Stream 0 codec", "mpeg2video", $probe["streams"][0]["codec_name"], $testOutput);
test("Stream 1", "audio", $probe["streams"][1]["codec_type"], $testOutput);
test("Stream 1 codec", "ac3", $probe["streams"][1]["codec_name"], $testOutput);
test("Stream 1 channel_layout", "5.1(side)", $probe["streams"][1]["channel_layout"], $testOutput);
test("Stream 1 channels", 6, $probe["streams"][1]["channels"], $testOutput);
test("Stream 2", "subtitle", $probe["streams"][2]["codec_type"], $testOutput);
test("Stream 2 codec", "dvd_subtitle", $probe["streams"][2]["codec_name"], $testOutput);
test("Stream 3 doesn't exist", FALSE, array_key_exists(3, $probe["streams"]), $testOutput);
test("Metadata Title", "Test No Input", $probe["format"]["tags"]["title"], $testOutput);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $testOutput);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $testOutput);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $testOutput);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $testOutput);

?>

