<?php

use PHPUnit\Framework\TestCase;

require_once "RipVideo.php";

abstract class Test extends TestCase {
	protected function getDataDir() {
		return getEnv("DATA_DIR");
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
		if (!file_exists($this->getDataDir() . DIRECTORY_SEPARATOR . $localFilename)) {
			$command =
				'curl -k -L -o "' .
				$this->getDataDir() .
				DIRECTORY_SEPARATOR .
				$localFilename .
				'" "' .
				getEnv("TEST_SAMPLE_BASE_URL") .
				"/" .
				$URLpath .
				'"';
			passthru($command, $ret);
			return 0 < $ret;
		}
		return true;
	}

	public function ripvideoBackup($filename, $args, $timeout = "8m") {
		$command = "timeout -s15 " . $timeout . " /opt/video-converter/src/rip-video.php --log-level=100";
		foreach ($args as $key => $value) {
			$command .= " " . $key;
			if (!is_bool($value)) {
				$command .= '="' . $value . '"';
			}
		}
		if (null !== $filename) {
			$command .= ' "' . $this->getDataDir() . DIRECTORY_SEPARATOR . $filename . '"';
		}
		print "Executing command: " . $command . "\n";
		passthru("cd \"" . $this->getDataDir() . "\"; " . $command, $return);
		print $command . " => returned value " . $return . "\n";
		return $return;
	}

	public function ripvideo($filename, $args, $timeout = "8m") {
		$options = [];
		foreach ($args as $key => $value) {
			$options[substr($key, 2)] = $value;
		}
		$options["filename"] = $filename;
		Options::init($options);
		$rip = new RipVideo();
		return $rip->rip();
	}
}
