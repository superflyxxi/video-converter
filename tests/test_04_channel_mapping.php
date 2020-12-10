<?php
include_once "common.php";

getFile("dvd.mkv", "https://".$sampleDomain."/samples/DVD_Sample.mkv");

test_ffmpeg(array("INPUT"=>"dvd.mkv", "TITLE"=>"Test Channel Mapping", "YEAR"=>"2019", "AUDIO_CHANNEL_LAYOUT"=>"stereo", "AUDIO_CHANNEL_LAYOUT_TRACKS"=>1, "VIDEO_TRACKS"=>-1, "SUBTITLE_TRACKS"=>-1), $output, $return);
test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Channel Mapping (2019).dvd.mkv.mkv");
$probe = json_decode($probe, true);

test("Stream 0", "audio", $probe["streams"][0]["codec_type"], $output);
test("Stream 0 codec", "aac", $probe["streams"][0]["codec_name"], $output);
test("Stream 0 channel_layout", "stereo", $probe["streams"][0]["channel_layout"], $output);
test("Stream 0 channels", 2, $probe["streams"][0]["channels"], $output);
test("Stream 1 exists", FALSE, array_key_exists(1, $probe["streams"]), $output);
test("Metadata Title", "Test Channel Mapping", $probe["format"]["tags"]["title"], $output);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $output);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $output);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $output);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $output);

?>

