<?php

$user = getEnv("UID");
$image = getEnv("THIS_REGISTRY").'/'.getEnv("THIS_REPO").'/'.getEnv("THIS_IMAGE").':'.getEnv("THIS_LABEL");


function test($message, $expected, $actual) {
	if ($expected !== $actual) {
		printf("FAIL: %s. Expected='%s', but got='%s'\n", $message, $expected, $actual);
		exit(1);
	}
	printf("PASS: %s. Got expected='%s'\n", $message, $expected, $actual);
}

function probe($file) {
	global $user;
	global $image;
	$command = 'docker run --rm -it --user='.$user.' -v `pwd`:/data --entrypoint ffprobe '.$image.' -v quiet -print_format json -show_format -show_streams "'.$file.'"';
#	$command = 'ffprobe -v quiet -print_format json -show_format -show_streams "'.$file.'"';
	printf("Probing '%s'\n", $file);
	exec($command, $out, $ret);
	if ($ret == 0) {
		return implode($out);
	}
	return NULL;
}

?>

