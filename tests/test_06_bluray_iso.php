<?php

include_once "common.php";
if (!file_exists(getEnv("TMP_DIR")."/test_bluray.iso")) {
	file_put_contents(getEnv("TMP_DIR")."/test_bluray.iso", fopen("http://superflyxxi.dlinkddns.com/samples/Some_Trailers.ISO", 'r'));
}

$command = 'docker run --rm -t -v `pwd`:/data -e INPUT=test_bluray.iso -e TITLE="Test BluRay ISO" -e YEAR=2019 '.$image;
printf("executing: %s\n", $command);
exec($command, $output, $return);

test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test BluRay ISO (2019).ffmpeg.mkv");
$probe = json_decode($probe, true);

test("Stream 0", "video", $probe["streams"][0]["codec_type"]);
test("Stream 0 codec", "hevc", $probe["streams"][0]["codec_name"]);
test("Stream 1", "audio", $probe["streams"][1]["codec_type"]);
test("Stream 1 codec", "aac", $probe["streams"][1]["codec_name"]);
test("Stream 1 channel_layout", "5.1", $probe["streams"][1]["channel_layout"]);
test("Stream 1 channels", 6, $probe["streams"][1]["channels"]);
test("Metadata Title", "Test default", $probe["format"]["tags"]["title"]);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"]);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]));
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]));
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]));

?>

