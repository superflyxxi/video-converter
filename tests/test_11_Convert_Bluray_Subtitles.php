<?php
include_once "common.php";

getFile("bluray.mkv", "https://superflyxxi.dlinkddns.com/samples/Bluray_Sample.mkv");

$command = 'docker run --rm -t -v ' . getEnv("TMP_DIR") . ':/data -e APPLY_POSTFIX=false -e INPUT=bluray.mkv -e TITLE="Test Convert Bluray Subtitle" -e VIDEO_TRACKS=-1 -e AUDIO_TRACKS=-1 -e YEAR=2019 ' . $image;
printf("executing: %s\n", $command);
exec($command, $output, $return);

test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Convert Bluray Subtitle (2019).mkv");
$probe = json_decode($probe, true);

test("Stream 0", "subtitle", $probe["streams"][0]["codec_type"], $output);
test("Stream 0 codec", getEnv("BUILD_SUBTITLE_CONVERT") == "false" ? "hdmv_pgs_subtitle" : "ass", $probe["streams"][0]["codec_name"], $output);
test("Stream 0 language", "eng", $probe["streams"][0]["tags"]["language"], $output);
test("Stream 1 exists", FALSE, array_key_exists(1, $probe["streams"]), $output);
test("Metadata Title", "Test Convert Bluray Subtitle", $probe["format"]["tags"]["title"], $output);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $output);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $output);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $output);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $output);

?>

