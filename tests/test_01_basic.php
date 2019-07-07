<?php

include_once "common.php";

$command = 'docker run --rm -t -v `pwd`:/data -e INPUT=test.mpg -e TITLE="Test default" -e YEAR=2019 '.$image;
printf("executing: %s\n", $command);
exec($command, $output, $return);
test("ffmpeg code", 0, $return);

$probe = probe("/data/Test default (2019).ffmpeg.mkv");
$probe = json_decode($probe, true);

test("Stream 0", "video", $probe["streams"][0]["codec_type"]);
test("Stream 0 codec", "hevc", $probe["streams"][0]["codec_name"]);
test("Stream 1", "audio", $probe["streams"][1]["codec_type"]);
test("Stream 1 codec", "aac", $probe["streams"][1]["codec_name"]);
test("Metadata Title", "Test default", $probe["format"]["tags"]["title"]);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"]);

?>

