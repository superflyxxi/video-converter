<?php

require_once "convert/ConvertFile.php";
require_once "request/CSVRequest.php";
require_once "LogWrapper.php";
require_once "Options.php";

class RipVideo {
	public static $log;

	public function rip() {
		if (null == Options::get("title")) {
			self::$log->error("title missing");
			return 1;
		}

		$envInput = Options::getInputFile();
		$csvRequest = null;
		if (strcasecmp(substr($envInput, -4), ".csv") === 0) {
			$csvRequest = new CSVRequest(new SplFileObject($envInput, "r"));
		} else {
			if (null == $envInput) {
				$arrFiles = array_diff(scandir("."), ["..", "."]);
			} else {
				$arrFiles[] = $envInput;
			}
			self::$log->debug("Files to process", ["arrFiles" => $arrFiles]);
			$csvFile = new SplTempFileObject();
			$csvFile->fputcsv(["filename", "dummy"]);
			foreach ($arrFiles as $infile) {
				self::$log->debug("Adding to CSV", ["filename" => $infile]);
				$csvFile->fputcsv([$infile, "dummy"]);
			}
			$csvFile->rewind();
			$csvRequest = new CSVRequest($csvFile);
		}

		return $csvRequest->convert();
	}
}

RipVideo::$log = new LogWrapper("RipVideo");
