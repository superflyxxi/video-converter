<?php

$user = getEnv("UID");
$image = getEnv("THIS_REGISTRY").'/'.getEnv("THIS_REPO").'/'.getEnv("THIS_IMAGE").':'.getEnv("THIS_LABEL");

function test($message, $expected, $actual, $extraLogs="") {
	if ($expected !== $actual) {
		printf("FAIL: %s. Expected='", $message);
		print_r($expected);
		printf("', but got '");
		print_r($actual);
		printf("'\n");
		print_r($extraLogs);
		printf("\n\n");
		exit(1);
	}
	printf("PASS: %s. Got expected='", $message);
	print_r($expected);
	printf("'\n");
}

function probe($file) {
	global $user;
	global $image;
	$command = 'docker run --rm -t';
	if ($user) {
		$command .= ' --user='.$user;
	}
	$command .= ' -v '.getEnv("TMP_DIR").':/data --entrypoint ffprobe '.$image.' -v quiet -print_format json -show_format -show_streams "'.$file.'"';
#	$command = 'ffprobe -v quiet -print_format json -show_format -show_streams "'.$file.'"';
	printf("Probing '%s'\n", $file);
	exec($command, $out, $ret);
	if ($ret == 0) {
		return implode($out);
	}
	return NULL;
}

function getFile($localFilename, $URL) {
	if (!file_exists(getEnv("TMP_DIR")."/".$localFilename)) {
		return 0 < file_put_contents(getEnv("TMP_DIR")."/".$localFilename, fopen($URL, 'r'));
	}
	return TRUE;
}
?>

