<?php

include_once "functions.php";

class OutputFile {
	
	public function __construct() {
		$this->envOutput = getEnv("OUTPUT");
		$this->outputDir = getEnvWithDefault("OUTPUT_DIR", "/data");
	}

	public $title = NULL;
	public $subtitle = NULL;
	public $year = NULL;
	public $season = NULL;
	public $episode = NULL;

	private $envOutput = NULL;
	private $outputDir = NULL;

	public function getOutputFile() {
		if (NULL != $this->envOutput) {
			return $this->envOutput;
		}
		$out = $this->outputDir."/".$this->title;
		if (NULL != $this->year) {
			$out .= " (".$this->year.")";
		} else if (NULL != $this->season) {
			$out .= " - s".$this->season."e".$this->episode;
		}
		if (NULL != $this->subtitle) {
			$out .= " ".$this->subtitle;
		}
		return $out;
	}
}

?>

