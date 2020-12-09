<?php
$user = getEnv("UID");
$image = getEnv("THIS_FULL_IMAGE");
$sampleDomain = getEnv("TEST_SAMPLE_DOMAIN");

printf("TEST: %s\n", debug_backtrace()[0]['file']);

function test($message, $expected, $actual, $extraLogs = "")
{
    if ($expected !== $actual) {
        printf("FAIL: %s. Expected='", $message);
        print_r($expected);
        printf("', but got '");
        print_r($actual);
        printf("'\n");
        print_r($extraLogs);
        printf("\n\n");
        flush();
        exit(1);
    }
    printf("PASS: %s. Got expected='", $message);
    print_r($expected);
    printf("'\n");
}

function probe($file)
{
    global $user;
    global $image;
    $command = 'docker run --rm -t';
    if ($user) {
        $command .= ' --user=' . $user;
    }
    $command .= ' -v ' . getEnv("TMP_DIR") . ':/data --entrypoint ffprobe ' . $image . ' -v quiet -print_format json -show_format -show_streams "' . $file . '"';
    printf("Probing '%s'\n", $file);
    exec($command, $out, $ret);
    if ($ret == 0) {
        $out = implode($out);
        printf("Result of Probe: %s\n\n", $out);
        return $out;
    }
    return NULL;
}

function getFile($localFilename, $URL)
{
    if (! file_exists(getEnv("TMP_DIR") . "/" . $localFilename)) {
        passthru('curl -k -L -o "' . getEnv("TMP_DIR") . '/' . $localFilename . '" "' . $URL . '"', $ret);
        return 0 < $ret;
    }
    return TRUE;
}

function test_ffmpeg($envVars, &$output, &$return, $timeout = "5m") {
    $command = 'timeout -s9 ' . $timeout . ' docker run -t --user ' . getEnv("UID").':'.getEnv("GID"). ' --name test -v "' . getEnv("TMP_DIR") . ':/data" ';
    foreach ($envVars as $key => $value) {
        $command .= " -e " . $key . '="' . $value . '" ';
    }
    $command .= getEnv("THIS_FULL_IMAGE");
    printf("%s: executing: %s\n", date(DateTimeInterface::ISO8601), $command);
    exec($command, $output, $return);
    exec("docker rm -f test");
    printf("%s: Done executing\n", date(DateTimeInterface::ISO8601));
}

?>
