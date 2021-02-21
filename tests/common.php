<?php
$user = getEnv("UID");
$image = getEnv("THIS_FULL_IMAGE");
//set_include_path(get_include_path() . PATH_SEPARATOR . "/home/ripvideo");
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

final class CommonTestUtil {
        private static $instance = NULL; 
        public static function getInstance(): CommonTestUtil {
            if (NULL == self::$instance) {
                self::$instance = new CommonTestUtil();
            }
            return self::$instance;
        }
        private function __construct() {
            $this->sampleDomain = getEnv("TEST_SAMPLE_DOMAIN");
        }
	private $sampleDomain;
public static function probe($file)
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
        return $out;
    }
    return NULL;
}

public function getFile($localFilename, $URLpath)
{
    if (! file_exists(getEnv("TMP_DIR") . "/" . $localFilename)) {
        passthru('curl -k -L -o "' . getEnv("TMP_DIR") . '/' . $localFilename . '" "https://' .$this->sampleDomain . $URLpath . '"', $ret);
        return 0 < $ret;
    }
    return TRUE;
}

    public function test_ffmpeg($envVars, &$output, &$return, $timeout = "8m") {
        //$command = 'timeout -s9 ' . $timeout . ' docker run -t --rm --user $(id -u):$(id -g) --name test -v "' . getEnv("TMP_DIR") . ':/data" ';
        $command = "";
        foreach ($envVars as $key => $value) {
            $command .= $key . '="' . $value . '" ';
        }
        $command .= 'timeout -s9 ' . $timeout . ' /home/ripvideo/rip-video ';
        //$command .= getEnv("THIS_FULL_IMAGE");
        printf("%s: executing: %s\n", date(DateTimeInterface::ISO8601), $command);
        passthru($command, $return);
        //exec($command, $output, $return);
        //exec("docker stop test");
        printf("%s: Done executing\n", date(DateTimeInterface::ISO8601));
}
}
?>
