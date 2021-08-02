<?php

use PHPUnit\Framework\TestCase;
abstract class Test extends TestCase {
        
    public function __construct() {
        parent::__construct();
        $this->sampleDomain = getEnv("TEST_SAMPLE_DOMAIN");
        $this->dataDir = getEnv("DATA_DIR");
    }
    private $sampleDomain;
    private $dataDir;

    protected function getDataDir() {
        return $this->dataDir;
    }

    protected function setUp(): void {
        parent::setUp();
        $tmpDir = exec("mktemp -d");
        exec("mkdir -p " . $tmpDir . DIRECTORY_SEPARATOR . "data");
        putenv("TMP_DIR=" . $tmpDir ); 
    }

    public function probe($filename)
    {
        $file = $this->getDataDir() . DIRECTORY_SEPARATOR . $filename;
        $this->assertFileExists($file, "File missing, cannot probe");
        $command = 'ffprobe -v quiet -print_format json -show_format -show_streams "' . $file . '"';
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
            case "bluray":
                $URLpath = "/samples/Bluray_Sample.mkv";
                $localFilename = "bluray.mkv";
                break;
        }
        if (! file_exists($this->dataDir . DIRECTORY_SEPARATOR . $localFilename)) {
            $command = 'curl -k -L -o "' . $this->dataDir . DIRECTORY_SEPARATOR . $localFilename . '" "https://' .$this->sampleDomain . $URLpath . '"';
            passthru($command, $ret);
            return 0 < $ret;
        }
        return TRUE;
    }

    public function ripvideo($envVars, $timeout = "8m") {
        $command = "";
        foreach ($envVars as $key => $value) {
            $command .= $key . '="' . $value . '" ';
        }
        $command .= 'timeout -s15 ' . $timeout . ' /app/ripvideo/rip-video.php';
        passthru($command, $return);
	print("Return value ". $return);
        return $return;
    }
}
?>
