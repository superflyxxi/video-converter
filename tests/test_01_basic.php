<?php

include_once "common.php";

getFile("test.mpg", "https://alcorn.com/wp-content/downloads/test-files/AC3AlcornTest_HD.mpg");

$command = 'docker run --rm -t -v '.getEnv("TMP_DIR").':/data -e INPUT=test.mpg -e TITLE="Test default" -e YEAR=2019 '.$image;
printf("executing: %s\n", $command);
exec($command, $output, $return);

test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test default (2019).ffmpeg.mkv");
$probe = json_decode($probe, true);

$testOutput = array($output, $probe);
test("Stream 0", "video", $probe["streams"][0]["codec_type"], $testOutput);
test("Stream 0 codec", "hevc", $probe["streams"][0]["codec_name"], $testOutput);
test("Stream 1", "audio", $probe["streams"][1]["codec_type"], $testOutput);
test("Stream 1 codec", "aac", $probe["streams"][1]["codec_name"], $testOutput);
test("Stream 1 channel_layout", "5.1", $probe["streams"][1]["channel_layout"], $testOutput);
test("Stream 1 channels", 6, $probe["streams"][1]["channels"], $testOutput);
test("Metadata Title", "Test default", $probe["format"]["tags"]["title"], $testOutput);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $testOutput);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $testOutput);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $testOutput);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $testOutput);

?>

