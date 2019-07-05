<?php

class OutputFile {
	
	public function __construct() {
		$this->envOutput = getEnv("OUTPUT");
		$this->outputDir = getEnv("OUTPUT_DIR");
	}

	public $title = NULL;
	public $subtitle = NULL;
	public $year = NULL;
	public $searson = NULL;
	public $episoe = NULL;

	private $envOutput = NULL;
	private $outputDir = NULL;

	public function getOutputFile() {
		if (NULL != $envOutput) {
			return $envOutput;
		}
	}
}

?>

