<?php
include_once "common.php";

getFile("bluray.mkv", "https://superflyxxi.dlinkddns.com/samples/Bluray_Sample.mkv");

$command = 'docker run --rm -t -v ' . getEnv("TMP_DIR") . ':/data -e APPLY_POSTFIX=false -e INPUT=bluray.mkv -e TITLE="Test Convert Bluray Subtitle" -e VIDEO_TRACKS="" -e AUDIO_TRACKS="" -e SUBTITLE_FORMAT=ass -e YEAR=2019 ' . $image;
printf("executing: %s\n", $command);
exec($command, $output, $return);

test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Convert Bluray Subtitle (2019).mkv");
$probe = json_decode($probe, true);

test("Stream 0", "subtitle", $probe["streams"][3]["codec_type"], $output);
test("Stream 0 codec", "ass", $probe["streams"][3]["codec_name"], $output);
test("Metadata Title", "Test Convert Bluray Subtitle", $probe["format"]["tags"]["title"], $output);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $output);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $output);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $output);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $output);

?>

