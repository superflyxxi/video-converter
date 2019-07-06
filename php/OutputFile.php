<?php

class OutputFile {
	
	public function __construct() {
		$this->envOutput = getEnv("OUTPUT");
		$this->outputDir = getEnv("OUTPUT_DIR");
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
		return "wip";
	}
}

?>

