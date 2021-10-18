<?php
require_once "LogWrapper.php";
require_once "request/Request.php";
require_once "OutputFile.php";
require_once "functions.php";
require_once "convert/ConvertVideo.php";
require_once "convert/ConvertSubtitle.php";
require_once "convert/ConvertAudio.php";
require_once "ffmpeg/FFmpegHelper.php";

class ConvertFile {
	public static $log;

	private $inputFilename = null;

	private $req = null;

	public function __construct($req) {
		$this->req = $req;
		$this->inputFilename = $this->req->oInputFile->getFileName();
	}

	public function convert() {
		self::$log->info("Starting conversion for file", [
			"filename" => $this->inputFilename,
		]);
		$oOutput = new OutputFile(
			getEnvWithDefault("APPLY_POSTFIX", "true") == "true"
				? basename($this->inputFilename)
				: null
		); // use inputfile as the postfix only if APPLY_POSTFIX is set
		$oOutput->title = $this->req->title;
		$oOutput->showTitle = $this->req->showTitle;
		$oOutput->season = $this->req->season;
		$oOutput->episode = $this->req->episode;
		$oOutput->year = $this->req->year;

		self::$log->debug("Conversion output", [
			"request" => $this->req,
			"output" => $oOutput,
		]);
		$allRequests[] = $this->req;
		$allRequests = array_merge(
			$allRequests,
			ConvertVideo::convert($this->req)
		);
		$allRequests = array_merge(
			$allRequests,
			ConvertAudio::convert($this->req)
		);
		$allRequests = array_merge(
			$allRequests,
			ConvertSubtitle::convert($this->req, $oOutput)
		);

		$returnValue = FFmpegHelper::execute($allRequests, $oOutput, false);
		self::$log->info("Completed conversion.");
		return $returnValue;
	}
}

ConvertFile::$log = new LogWrapper("ConvertFile");
?>
