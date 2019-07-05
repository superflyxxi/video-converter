<?php

class OutputFile {
	public function __construct() { }

	public $title = NULL;
	public $subtitle = NULL;
	public $year = NULL;
	public $searson = NULL;
	public $episoe = NULL;

	private $envOutput = getEnv("OUTPUT");
	private $outputDir = getEnv("OUTPUT_DIR");

	public function getOutputFile() {
		if (NULL != $envOutput) {
			return $envOutput;
		}
	}
}

?>

