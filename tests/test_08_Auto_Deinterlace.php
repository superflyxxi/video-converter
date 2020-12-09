<?php
include_once "common.php";

getFile("dvd.mkv", "https://".$sampleDomain."/samples/DVD_Sample.mkv");

test_ffmpeg(array("INPUT"=>"dvd.mkv", "AUDIO_TRACKS"=>-1, "SUBTITLE_TRACKS"=>-1, "TITLE"=>"Test Auto Deinterlace", "YEAR"=>2019), $output, $return);
//$command = 'docker run --rm -t --name test08 -v ' . getEnv("TMP_DIR") . ':/data -e INPUT=dvd.mkv -e AUDIO_TRACKS=-1 -e SUBTITLE_TRACKS=-1 -e TITLE="Test Auto Deinterlace" -e YEAR=2019 ' . $image;
//printf("executing: %s\n", $command);
//exec("timeout -s9 5m ". $command, $output, $return);
//exec("docker stop test08");

//test("ffmpeg code", 0, $return, $output);

$probe = probe("/data/Test Auto Deinterlace (2019).dvd.mkv.mkv");
print_r("Result of probe: ");
print_r($probe);
$probe = json_decode($probe, true);

test("Stream 0", "video", $probe["streams"][0]["codec_type"], $output);
test("Stream 0 codec", "hevc", $probe["streams"][0]["codec_name"], $output);
test("Stream 1 exists", FALSE, array_key_exists(1, $probe["streams"]), $output);
test("Metadata Title", "Test Auto Deinterlace", $probe["format"]["tags"]["title"], $output);
test("Metadata YEAR", "2019", $probe["format"]["tags"]["YEAR"], $output);
test("Metadata SEASON", FALSE, array_key_exists("SEASON", $probe["format"]["tags"]), $output);
test("Metadata EPISODE", FALSE, array_key_exists("EPISODE", $probe["format"]["tags"]), $output);
test("Metadata SUBTITLE", FALSE, array_key_exists("SUBTITLE", $probe["format"]["tags"]), $output);

?>

