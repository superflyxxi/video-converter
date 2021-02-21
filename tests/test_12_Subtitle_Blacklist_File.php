<?php
include_once "common.php";

getFile("dvd.mkv", "https://".$sampleDomain."/samples/DVD_Sample.mkv");

test_ffmpeg(array("APPLY_POSTFIX"=>"false", "INPUT"=>"dvd.mkv", "TITLE"=>"Test Subtitle Files", "VIDEO_FORMAT"=>"copy", "AUDIO_TRACKS"=>-1, "SUBTITLE_FORMAT"=>"srt", "SUBTITLE_CONVERSION_OUTPUT"=>"FILE", "SUBTITLE_CONVERSION_BLACKLIST"=>"\`!\�~@~", "YEAR"=>2019), $output, $return);

test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Subtitle Files (2019).mkv");
$probe = json_decode($probe, true);

$testOutput = array($output, $probe);

test("Stream 0", "video", $probe["streams"][0]["codec_type"], $testOutput);
test("Stream 1 exists", FALSE, array_key_exists(1, $probe["streams"]), array($probe, $testOutput));
test("Metadata Title", "Test Subtitle Files", $probe["format"]["tags"]["title"], $testOutput);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $testOutput);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $testOutput);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $testOutput);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $testOutput);

$testfile = getEnv("TMP_DIR")."/Test Subtitle Files (2019).mkv.2-eng.srt";
test("File exists, ".$testfile, TRUE, file_exists($testfile));
$contents = file_get_contents($testfile);
test("SRT Contains ’", FALSE, strpos($contents, "’"), array_merge($testOutput, array($contents)));
test("SRT Contains !", FALSE, strpos($contents, "!"), array_merge($testOutput, array($contents)));
?>
