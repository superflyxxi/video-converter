<?php

$user = getEnv("UID");
$image = getEnv("THIS_REGISTRY").'/'.getEnv("THIS_REPO").'/'.getEnv("THIS_IMAGE").':'.getEnv("THIS_LABEL");


function test($message, $expected, $actual) {
	if ($expected !== $actual) {
		printf("%s. Expected=%s, but got=%s", $message, $expected, $actual);
		exit(1);
	}
}

?>

