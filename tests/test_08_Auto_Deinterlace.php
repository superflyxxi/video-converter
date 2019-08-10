<?php
include_once "common.php";
include_once "../php/FFmpegHelper.php";


getFile("test.mpg", "https://alcorn.com/wp-content/downloads/test-files/AC3AlcornTest_HD.mpg");

$ret = FFmpegHelper::isInterlaced(getEnv("TMP_DIR")."/test.mpg");
printf("Result: %s\n", $ret == TRUE ? "true" : "false");
test("Is interlaced", FALSE, $ret);

?>

