<?php
include_once "common.php";

getFile("dvd.mkv", "https://superflyxxi.dlinkddns.com/samples/DVD_Sample.mkv");

$command = 'docker run --rm -t -v ' . getEnv("TMP_DIR") . ':/data -e APPLY_POSTFIX=false -e INPUT=dvd.mkv -e TITLE="Test Subtitle Files" -e VIDEO_FORMAT=copy -e AUDIO_TRACKS=-1 -e SUBTITLE_FORMAT=srt -e SUBTITLE_CONVERSION_OUTPUT=FILE -e YEAR=2019 ' . $image;
printf("executing: %s\n", $command);
exec($command, $output, $return);

test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Subtitle Files (2019).mkv");
$probe = json_decode($probe, true);

test("Stream 0", "video", $probe["streams"][0]["codec_type"], $output);
test("Stream 1 exists", FALSE, array_key_exists(1, $probe["streams"]), $output);
test("Metadata Title", "Test Subtitle Files", $probe["format"]["tags"]["title"], $output);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $output);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $output);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $output);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $output);

$testfile = getEnv("TMP_DIR")."/Test Subtitle Files (2019).mkv.2-eng.srt";
test("File exists, ".$testfile, TRUE, file_exists($testfile));
?>

