<?php
/*
 * Tests when no input is given. It should product results for the file as expected.
 */
include_once "common.php";

getFile("dvd.mkv", "https://superflyxxi.dlinkddns.com/samples/DVD_Sample.mkv");

$command = 'docker run --rm -t -v ' . getEnv("TMP_DIR") . ':/data -e INPUT=dvd.mkv -e TITLE="Test Input" -e YEAR=2019 -e AUDIO_FORMAT=copy -e VIDEO_FORMAT=copy -e SUBTITLE_FORMAT=copy ' . $image;
printf("executing: %s\n", $command);
exec($command, $output, $return);

test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Input (2019).dvd.mkv.mkv");
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
test("Metadata Title", "Test Input", $probe["format"]["tags"]["title"], $testOutput);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $testOutput);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $testOutput);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $testOutput);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $testOutput);

?>

