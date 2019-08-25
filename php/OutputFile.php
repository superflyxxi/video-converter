<?php
include_once "functions.php";

class OutputFile
{

    public function __construct($postfix = NULL, $out = NULL, $dir = NULL)
    {
        $this->postfix = $postfix == NULL ? "ffmpeg" : $postfix;
        $this->envOutput = $out == NULL ? getEnv("OUTPUT") : $out;
        $this->outputDir = $dir == NULL ? getEnvWithDefault("OUTPUT_DIR", "/data") : $dir;
    }

    public $title = NULL;

    public $subtitle = NULL;

    public $year = NULL;

    public $season = NULL;

    public $episode = NULL;

    public $format = NULL;

    private $envOutput = NULL;

    private $outputDir = NULL;

    private $postfix = NULL;

    public function getFileName()
    {
        if (NULL != $this->envOutput) {
            return $this->envOutput;
        }
        $out = $this->outputDir . "/" . $this->title;
        if (NULL != $this->year) {
            $out .= " (" . $this->year . ")";
        }
        if (NULL != $this->season) {
            $out .= " - s" . $this->season . "e" . $this->episode;
        }
        if (NULL != $this->subtitle) {
            $out .= " - " . $this->subtitle;
        }
        $out .= "." . $this->postfix . ".mkv";
        return $out;
    }
}

?>
