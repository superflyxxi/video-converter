<?php
include_once "common.php";

getFile("movie.mkv", "https://superflyxxi.dlinkddns.com/samples/Movie_Sample.mkv");

$command = 'docker run --rm -t -v ' . getEnv("TMP_DIR") . ':/data -e APPLY_POSTFIX=false -e INPUT=movie.mkv -e TITLE="Test Convert Subtitle" -e VIDEO_FORMAT=copy -e AUDIO_FORMAT=copy -e SUBTITLE_FORMAT=srt -e YEAR=2019 ' . $image;
printf("executing: %s\n", $command);
exec($command, $output, $return);

test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Convert Subtitle (2019).mkv");
$probe = json_decode($probe, true);

test("Stream 3", "subtitle", $probe["streams"][3]["codec_type"], $output);
test("Stream 3 codec", "srt", $probe["streams"][3]["codec_name"], $output);
test("Stream 0", "video", $probe["streams"][0]["codec_type"], $output);
test("Stream 0 codec", "mpeg2video", $probe["streams"][0]["codec_name"], $output);
test("Stream 1", "audio", $probe["streams"][1]["codec_type"], $output);
test("Stream 1 codec", "ac3", $probe["streams"][1]["codec_name"], $output);
test("Stream 1 channels", 6, $probe["streams"][1]["channels"], $output);
test("Stream 1 channel_layout", "5.1(side)", $probe["streams"][1]["channel_layout"], $output);
test("Metadata Title", "Test Convert Subtitle", $probe["format"]["tags"]["title"], $output);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $output);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $output);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $output);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $output);

?>

