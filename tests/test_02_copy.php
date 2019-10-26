<?php
include_once "common.php";

getFile("bluray.mkv", "https://superflyxxi.dlinkddns.com/samples/Bluray_Sample.mkv");

$command = 'docker run --rm -t -v ' . getEnv("TMP_DIR") . ':/data -e INPUT=bluray.mkv -e TITLE="Test copy" -e YEAR=2019 -e AUDIO_FORMAT=copy -e VIDEO_FORMAT=copy -e SUBTITLE_FORMAT=copy ' . $image;
printf("executing: %s\n", $command);
exec($command, $output, $return);
test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test copy (2019).bluray.mkv.mkv");
$probe = json_decode($probe, true);

test("Stream 0", "video", $probe["streams"][0]["codec_type"], $output);
test("Stream 0 codec", "vc1", $probe["streams"][0]["codec_name"], $output);
test("Stream 1", "audio", $probe["streams"][1]["codec_type"], $output);
test("Stream 1 codec", "dts", $probe["streams"][1]["codec_name"], $output);
test("Stream 1 channel_layout", "5.1(side)", $probe["streams"][1]["channel_layout"], $output);
test("Stream 1 channels", 6, $probe["streams"][1]["channels"], $output);
test("Stream 2", "subtitle", $probe["streams"][2]["codec_type"], $output);
test("Stream 2 codec", "hdmv_pgs_subtitle", $probe["streams"][2]["codec_name"], $output);
test("Metadata Title", "Test copy", $probe["format"]["tags"]["title"], $output);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $output);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $output);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $output);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $output);

?>

