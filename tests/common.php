<?php
$user = getEnv("UID");
$image = getEnv("THIS_FULL_IMAGE");
set_include_path(get_include_path() . PATH_SEPARATOR . "/home/ripvideo");

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

use PHPUnit\Framework\TestCase;
class Test extends TestCase {
        
    public function __construct() {
        parent::__construct();
        $this->sampleDomain = getEnv("TEST_SAMPLE_DOMAIN");
        $this->tmpDir = getEnv("TMP_DIR");
    }
    private $sampleDomain;
    private $tmpDir;

    public function probe($file)
    {
        global $user;
        global $image;
        //$command = 'docker run --rm -t';
        //if ($user) {
        //    $command .= ' --user=' . $user;
        //}
        //$command .= ' -v ' . getEnv("TMP_DIR") . ':/data --entrypoint ffprobe ' . $image . ' -v quiet -print_format json -show_format -show_streams "' . $file . '"';
        $command = 'ffprobe -v quiet -print_format json -show_format -show_streams "' . $this->tmpDir . DIRECTORY_SEPARATOR . $file . '"';
        //printf("Probing '%s'\nCommand: %s\n", $file, $command);
        exec($command, $out, $ret);
        if ($ret == 0) {
            $out = implode($out);
            return $out;
        }
        return NULL;
    }

    public function getFile($file) {
        switch ($file) {
            case "dvd":
                $URLpath = "/samples/DVD_Sample.mkv";
                $localFilename = "dvd.mkv";
                break;

            case "bluray":
                $URLpath = "/samples/Bluray_Sample.mkv";
                $localFilename = "bluray.mkv";
                break;
        }
        if (! file_exists(getEnv("TMP_DIR") . "/" . $localFilename)) {
            passthru('curl -k -L -o "' . getEnv("TMP_DIR") . '/' . $localFilename . '" "https://' .$this->sampleDomain . $URLpath . '"', $ret);
            return 0 < $ret;
        }
        return TRUE;
    }

    public function ripvideo($envVars, &$output, &$return, $timeout = "8m") {
        //$command = 'timeout -s9 ' . $timeout . ' docker run -t --rm --user $(id -u):$(id -g) --name test -v "' . getEnv("TMP_DIR") . ':/data" ';
        $command = "";
        foreach ($envVars as $key => $value) {
            $command .= $key . '="' . $value . '" ';
        }
        $command .= 'timeout -s9 ' . $timeout . ' /home/ripvideo/rip-video ';
        //$command .= getEnv("THIS_FULL_IMAGE");
        //printf("%s: executing: %s\n", date(DateTimeInterface::ISO8601), $command);
        //passthru($command, $return);
        exec($command, $output, $return);
        //exec("docker stop test");
        //printf("%s: Done executing\n", date(DateTimeInterface::ISO8601));
}
}
?>
