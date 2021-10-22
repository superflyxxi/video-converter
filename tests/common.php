<?php

use PHPUnit\Framework\TestCase;

abstract class Test extends TestCase {
	public function __construct() {
		parent::__construct();
		$this->sampleBaseUrl = getEnv("TEST_SAMPLE_BASE_URL");
		$this->dataDir = getEnv("DATA_DIR");
	}
	private $sampleBaseUrl;
	private $dataDir;

	protected function getDataDir() {
		return $this->dataDir;
	}

	protected function setUp(): void {
		parent::setUp();
		$tmpDir = exec("mktemp -d");
		exec("mkdir -p " . $tmpDir . DIRECTORY_SEPARATOR . "data");
		putenv("TMP_DIR=" . $tmpDir);
	}

	public function probe($filename) {
		$file = $this->getDataDir() . DIRECTORY_SEPARATOR . $filename;
		$this->assertFileExists($file, "File missing, cannot probe");
		$command = 'ffprobe -v quiet -print_format json -show_format -show_streams "' . $file . '"';
		exec($command, $out, $ret);
		if ($ret == 0) {
			$out = implode($out);
			return json_decode($out, true);
		}
		return null;
	}

	public function getFile($file) {
		switch ($file) {
			case "dvd.mkv":
			case "dvd":
				$URLpath = "DVD_Sample.mkv";
				$localFilename = "dvd.mkv";
				break;

			case "bluray.mkv":
			case "bluray":
				$URLpath = "Bluray_Sample.mkv";
				$localFilename = "bluray.mkv";
				break;

			default:
				break;
		}
		if (!file_exists($this->dataDir . DIRECTORY_SEPARATOR . $localFilename)) {
			$command =
				'curl -k -L -o "' .
				$this->dataDir .
				DIRECTORY_SEPARATOR .
				$localFilename .
				'" "' .
				$this->sampleBaseUrl .
				"/" .
				$URLpath .
				'"';
			passthru($command, $ret);
			return 0 < $ret;
		}
		return true;
	}

	public function ripvideo($filename, $args, $timeout = "8m") {
		$command = "timeout -s15 " . $timeout . " video-converter --log-level=100";
		foreach ($args as $key => $value) {
			$command .= " " . $key;
			if (!is_bool($value)) {
				$command .= '="' . $value . '"';
			}
		}
		if (null !== $filename) {
			$command .= ' "' . $this->dataDir . DIRECTORY_SEPARATOR . $filename . '"';
		}
		print "Executing command: " . $command . "\n";
		passthru("cd \"" . $this->dataDir . "\"; " . $command, $return);
		print $command . " => returned value " . $return . "\n";
		return $return;
	}
}
