<?php
require_once "functions.php";

class OutputFile {
	public function __construct($postfix = null, $out = null, $dir = null) {
		$this->postfix = $postfix;
		$this->envOutput = $out == null ? getEnv("OUTPUT") : $out;
		$this->outputDir = $dir == null ? getEnvWithDefault("OUTPUT_DIR", "/data") : $dir;
	}

	public $title = null;

	public $showTitle = null;

	public $year = null;

	public $season = null;

	public $episode = null;

	public $format = null;

	private $envOutput = null;

	private $outputDir = null;

	private $postfix = null;

	public function getFileName() {
		if (null != $this->envOutput) {
			return $this->envOutput;
		}
		$out = $this->outputDir . "/" . $this->title;
		if (null != $this->year) {
			$out .= " (" . $this->year . ")";
		}
		if (null != $this->season) {
			$out .= " - s" . $this->season . "e" . $this->episode;
		}
		if (null != $this->showTitle) {
			$out .= " - " . $this->showTitle;
		}
		if (null != $this->postfix) {
			$out .= "." . $this->postfix;
		}
		$out .= ".mkv";
		return $out;
	}
}

?>
