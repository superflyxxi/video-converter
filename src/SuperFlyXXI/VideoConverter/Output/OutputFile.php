<?php
namespace SuperFlyXXI\VideoConverter\Output;

class OutputFile
{
    public function __construct($postfix = null, $out = null)
    {
        $this->postfix = $postfix;
        $this->envOutput = $out == null ? getEnv("OUTPUT") : $out;
    }

    public $title = null;

    public $showTitle = null;

    public $year = null;

    public $season = null;

    public $episode = null;

    public $format = null;

    private $envOutput = null;

    private $postfix = null;

    public function getFileName()
    {
        if (null != $this->envOutput) {
            return $this->envOutput;
        }
        $out = $this->title;
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
