<?php
set_include_path(get_include_path() . PATH_SEPARATOR . "/home/ripvideo");

//require_once "Logger.php";

use PHPUnit\Framework\TestCase;
class Test extends TestCase {
        
    public function __construct() {
        parent::__construct();
        $this->sampleDomain = getEnv("TEST_SAMPLE_DOMAIN");
        $this->dataDir = getEnv("DATA_DIR");
    }
    private $sampleDomain;
    private $dataDir;

    public function probe($file)
    {
        $command = 'ffprobe -v quiet -print_format json -show_format -show_streams "' . $this->dataDir . DIRECTORY_SEPARATOR . $file . '"';
        exec($command, $out, $ret);
        if ($ret == 0) {
            $out = implode($out);
            return json_decode($out, true);
        }
        return NULL;
    }

    public function getFile($file) {
        switch ($file) {
            case "dvd.mkv":
            case "dvd":
                $URLpath = "/samples/DVD_Sample.mkv";
                $localFilename = "dvd.mkv";
                break;

            case "bluray.mkv":
                $URLpath = "/samples/Bluray_Sample.mkv";
                $localFilename = "bluray.mkv";
                break;
        }
        if (! file_exists($this->dataDir . DIRECTORY_SEPARATOR . $localFilename)) {
            $command = 'curl -k -L -o "' . $this->dataDir . DIRECTORY_SEPARATOR . $localFilename . '" "https://' .$this->sampleDomain . $URLpath . '"';
            //Logger::debug("Executing %s\n", $command);
            //print_r($command . "\n");
            passthru($command, $ret);
            return 0 < $ret;
        }
        return TRUE;
    }

    public function ripvideo($envVars, &$output, &$return, $timeout = "8m") {
        $command = "";
        foreach ($envVars as $key => $value) {
            $command .= $key . '="' . $value . '" ';
        }
        $command .= 'timeout -s9 ' . $timeout . ' /home/ripvideo/rip-video ';
        passthru($command, $return);
        //exec($command, $output, $return);
    }
}
?>
