<?php

include_once "common.php";

getFile("test.mpg", "https://alcorn.com/wp-content/downloads/test-files/AC3AlcornTest_HD.mpg");

$command = 'docker run --rm -t -v '.getEnv("TMP_DIR").':/data -e INPUT=test.mpg -e TITLE="Test Normalize Track 1" -e YEAR=2019 -e NORMALIZE_AUDIO_TRACKS=1 '.$image;
printf("executing: %s\n", $command);
exec($command, $output, $return);

test("ffmpeg code", 0, $return);

$probe = probe("/data/Test Normalize Track 1 (2019).ffmpeg.mkv");
$probe = json_decode($probe, true);

test("Stream 0", "video", $probe["streams"][0]["codec_type"]);
test("Stream 0 codec", "hevc", $probe["streams"][0]["codec_name"]);
test("Stream 1", "audio", $probe["streams"][1]["codec_type"]);
test("Stream 1 codec", "aac", $probe["streams"][1]["codec_name"]);
test("Stream 1 channels", 6, $probe["streams"][1]["channels"]);
test("Stream 1 channel_layout", "5.1(side)", $probe["streams"][1]["channel_layout"]);
test("Stream 2", "audio", $probe["streams"][2]["codec_type"]);
test("Stream 2 codec", "aac", $probe["streams"][2]["codec_name"]);
test("Stream 2 channel_layout", "5.1(side)", $probe["streams"][2]["channel_layout"]);
test("Stream 2 channels", 6, $probe["streams"][2]["channels"]);
test("Stream 2 title", "Normalized  5.1(side)", $probe["streams"][2]["tags"]["title"]);
test("Metadata Title", "Test Normalize Track 1", $probe["format"]["tags"]["title"]);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"]);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]));
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]));
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]));

?>

