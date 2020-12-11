<?php
include_once "common.php";

getFile("dvd.mkv", "https://".$sampleDomain."/samples/DVD_Sample.mkv");

test_ffmpeg(array("INPUT"=>"dvd.mkv", "TITLE"=>"Test tv show", "YEAR"=>2019, "SEASON"=>"01", "EPISODE"=>"23", "SUBTITLE"=>"The One Where Things", "VIDEO_FORMAT"=>"copy", "AUDIO_FORMAT"=>"copy", "SUBTITLE_FORMAT"=>"copy"), $output, $return);
test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test tv show (2019) - s01e23 - The One Where Things.dvd.mkv.mkv");
$probe = json_decode($probe, true);

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
test("Metadata Title", "Test tv show", $probe["format"]["tags"]["title"], $testOutput);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $testOutput);
test("Metadata SEASON", "01", $probe["format"]["tags"]["SEASON"], $testOutput);
test("Metadata EPISODE", "23", $probe["format"]["tags"]["EPISODE"], $testOutput);
test("Metadata SUBTITLE", "The One Where Things", $probe["format"]["tags"]["SUBTITLE"], $testOutput);

?>

