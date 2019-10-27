<?php
include_once "common.php";

getFile("dvd.mkv", "https://superflyxxi.dlinkddns.com/samples/DVD_Sample.mkv");

$command = 'docker run --rm -t -v ' . getEnv("TMP_DIR") . ':/data -e INPUT=dvd.mkv -e TITLE="Test Normalize Track 1" -e YEAR=2019 -e NORMALIZE_AUDIO_TRACKS=1 -e VIDEO_TRACKS=-1 -e SUBTITLE_TRACKS=-1 ' . $image;
printf("executing: %s\n", $command);
exec($command, $output, $return);

test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Normalize Track 1 (2019).dvd.mkv.mkv");
$probe = json_decode($probe, true);

test("Stream 0", "audio", $probe["streams"][0]["codec_type"], $output);
test("Stream 0 codec", "aac", $probe["streams"][0]["codec_name"], $output);
test("Stream 0 channels", 6, $probe["streams"][0]["channels"], $output);
test("Stream 0 channel_layout", "5.1", $probe["streams"][0]["channel_layout"], $output);
test("Stream 1", "audio", $probe["streams"][1]["codec_type"], $output);
test("Stream 1 codec", "aac", $probe["streams"][1]["codec_name"], $output);
test("Stream 1 channel_layout", "5.1", $probe["streams"][1]["channel_layout"], $output);
test("Stream 1 channels", 6, $probe["streams"][1]["channels"], $output);
test("Stream 1 title", "Normalized eng 5.1", $probe["streams"][1]["tags"]["title"], $output);
test("Stream 2 doesn't exist", FALSE, array_key_exists(2, $probe["streams"]), $output);
test("Metadata Title", "Test Normalize Track 1", $probe["format"]["tags"]["title"], $output);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $output);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $output);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $output);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $output);

?>

