<?php
include_once "common.php";

getFile("dvd.mkv", "https://superflyxxi.dlinkddns.com/samples/DVD_Sample.mkv");

$command = 'docker run --rm -t -v ' . getEnv("TMP_DIR") . ':/data -e INPUT=dvd.mkv -e TITLE="Test Channel Mapping" -e YEAR=2019 -e AUDIO_CHANNEL_LAYOUT=stereo -e AUDIO_CHANNEL_LAYOUT_TRACKS=1 -e VIDEO_FORMAT=copy -e SUBTITLE_FORMAT=copy ' . $image;
printf("executing: %s\n", $command);
exec($command, $output, $return);
test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Channel Mapping (2019).dvd.mkv.mkv");
$probe = json_decode($probe, true);

test("Stream 0", "video", $probe["streams"][0]["codec_type"], $output);
test("Stream 0 codec", "mpeg2video", $probe["streams"][0]["codec_name"], $output);
test("Stream 1", "audio", $probe["streams"][1]["codec_type"], $output);
test("Stream 1 codec", "aac", $probe["streams"][1]["codec_name"], $output);
test("Stream 1 channel_layout", "stereo", $probe["streams"][1]["channel_layout"], $output);
test("Stream 1 channels", 2, $probe["streams"][1]["channels"], $output);
test("Metadata Title", "Test Channel Mapping", $probe["format"]["tags"]["title"], $output);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $output);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $output);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $output);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $output);

?>

