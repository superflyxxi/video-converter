<?php
include_once "common.php";

getFile("dvd.mkv", "https://".$sampleDomain."/samples/DVD_Sample.mkv");

test_ffmpeg(array("INPUT"=>"dvd.mkv", "DEINTERLACE_MODE"=>"00", "DEINTERLACE_CHECK"=>"idet", "AUDIO_TRACKS"=>-1, "SUBTITLE_TRACKS"=>-1, "TITLE"=>"Test Idet Auto Deinterlace", "YEAR"=>2021), $output, $return);

// not validating return as it can be killed; test("ffmpeg code", 0, $return, $testOutput);

$probe = probe("/data/Test Idet Auto Deinterlace (2021).dvd.mkv.mkv");
$probe = json_decode($probe, true);

$testOutput = array($output, $probe);

test("Stream 0", "video", $probe["streams"][0]["codec_type"], $testOutput);
test("Stream 0 codec", "hevc", $probe["streams"][0]["codec_name"], $testOutput);
// doesn't exist... test("Stream 0 field_order", "progressive", $probe["streams"][0]["field_order"], $testOutput);
test("Stream 0 r_frame_rate", "24000/1001", $probe["streams"][0]["r_frame_rate"], $testOutput);
test("Stream 1 exists", FALSE, array_key_exists(1, $probe["streams"]), $testOutput);
test("Metadata Title", "Test Idet Auto Deinterlace", $probe["format"]["tags"]["title"], $testOutput);
test("Metadata YEAR", "2021", $probe["format"]["tags"]["YEAR"], $testOutput);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $testOutput);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $testOutput);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $testOutput);

?>
