<?php
include_once "common.php";

getFile("dvd.mkv", "https://superflyxxi.dlinkddns.com/samples/DVD_Sample.mkv");

$command = 'docker run --rm -t -v ' . getEnv("TMP_DIR") . ':/data -e INPUT=dvd.mkv -e AUDIO_TRACKS="" -e SUBTITLE_TRACKS="" -e TITLE="Test Auto Deinterlace" -e YEAR=2019 ' . $image;
printf("executing: %s\n", $command);
exec($command, $output, $return);

test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Auto Deinterlace (2019).dvd.mkv.mkv");
$probe = json_decode($probe, true);

print_r($probe);

test("Stream 0", "video", $probe["streams"][0]["codec_type"], $output);
test("Stream 0 codec", "hevc", $probe["streams"][0]["codec_name"], $output);
test("Stream 1 exists", FALSE, array_key_exists(1, $probe["streams"], $output);
test("Metadata Title", "Test Normalize Track 1", $probe["format"]["tags"]["title"], $output);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $output);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $output);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $output);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $output);

?>

