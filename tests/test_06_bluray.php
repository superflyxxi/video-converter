<?php
include_once "common.php";

getFile("test_bluray.iso", "http://superflyxxi.dlinkddns.com/samples/MARVEL_Trailers_BLURAY.ISO");

$command = 'docker run --rm -t -v ' . getEnv("TMP_DIR") . ':/data -e INPUT=test_bluray.iso -e TITLE="Test Bluray Default" -e YEAR=2019 ' . $image;
printf("executing: %s\n", $command);
passthru($command, $return);

test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Normalize Track 1 (2019).test.mpg.mkv");
$probe = json_decode($probe, true);

test("Stream 0", "video", $probe["streams"][0]["codec_type"], $output);
test("Stream 0 codec", "hevc", $probe["streams"][0]["codec_name"], $output);
test("Stream 1", "audio", $probe["streams"][1]["codec_type"], $output);
test("Stream 1 codec", "aac", $probe["streams"][1]["codec_name"], $output);
test("Stream 1 channels", 6, $probe["streams"][1]["channels"], $output);
test("Stream 1 channel_layout", "5.1", $probe["streams"][1]["channel_layout"], $output);
test("Stream 2", "audio", $probe["streams"][2]["codec_type"], $output);
test("Stream 2 codec", "aac", $probe["streams"][2]["codec_name"], $output);
test("Stream 2 channel_layout", "5.1", $probe["streams"][2]["channel_layout"], $output);
test("Stream 2 channels", 6, $probe["streams"][2]["channels"], $output);
test("Stream 2 title", "Normalized  5.1", $probe["streams"][2]["tags"]["title"], $output);
test("Metadata Title", "Test Normalize Track 1", $probe["format"]["tags"]["title"], $output);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $output);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $output);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $output);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $output);

?>

