<?php
include_once "common.php";

getFile("test.mpg", "https://alcorn.com/wp-content/downloads/test-files/AC3AlcornTest_HD.mpg");

$command = 'docker run --rm -t -v ' . getEnv("TMP_DIR") . ':/data -e INPUT=test.mpg -e TITLE="Test tv show" -e SEASON=01 -e EPISODE=23 -e SUBTITLE="The One Where Things" ' . $image;
printf("executing: %s\n", $command);
exec($command, $output, $return);
test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test tv show - s01e23 - The One Where Things.ffmpeg.mkv");
$probe = json_decode($probe, true);

test("Stream 0", "video", $probe["streams"][0]["codec_type"], $output);
test("Stream 0 codec", "hevc", $probe["streams"][0]["codec_name"], $output);
test("Stream 1", "audio", $probe["streams"][1]["codec_type"], $output);
test("Stream 1 codec", "aac", $probe["streams"][1]["codec_name"], $output);
test("Stream 1 channel_layout", "5.1", $probe["streams"][1]["channel_layout"], $output);
test("Stream 1 channels", 6, $probe["streams"][1]["channels"], $output);
test("Metadata Title", "Test tv show", $probe["format"]["tags"]["title"], $output);
test("Metadata SEASON", "01", $probe["format"]["tags"]["SEASON"], $output);
test("Metadata EPISODE", "23", $probe["format"]["tags"]["EPISODE"], $output);
test("Metadata SUBTITLE", "The One Where Things", $probe["format"]["tags"]["SUBTITLE"], $output);
test("Metadata YEAR", FALSE, array_key_exists("YEAR", $probe["format"]["tags"]), $output);

?>
