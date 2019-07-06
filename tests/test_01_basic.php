<?php

include_once "common.php";

exec('docker run --rm -it --user='.$user.' -v `pwd`:/data -e INPUT=test.mpg -e TITLE="Test default" -e YEAR=2019 '.$image, $output, $return);
test("ffmpeg code", 0, $return);

exec('docker run --rm -it --user='.$user.' -v `pwd`:/data --entrypoint ffprobe '.$image.' -i "/data/Test default (2019).ffmpeg.mkv"', $output, $return);
test("ffprobe", 0, $return);

?>

