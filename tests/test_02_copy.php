<?php

include_once "common.php";

$command = 'docker run --rm -it -v `pwd`:/data -e INPUT=test.mpg -e TITLE="Test copy" -e YEAR=2019 -e AUDIO_FORMAT=copy -e VIDEO_FORMAT=copy '.$image;
printf("executing: %s\n", $command);
exec($command, $output, $return);
test("ffmpeg code", 0, $return);

$probe = probe("/data/Test copy (2019).ffmpeg.mkv");
$probe = json_decode($probe, true);

test("Stream 0", "video", $probe["streams"][0]["codec_type"]);
test("Stream 0 codec", "mpeg2video", $probe["streams"][0]["codec_name"]);
test("Stream 1", "audio", $probe["streams"][1]["codec_type"]);
test("Stream 1 codec", "ac3", $probe["streams"][1]["codec_name"]);
test("Metadata Title", "Test copy", $probe["format"]["tags"]["title"]);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"]);

?>

