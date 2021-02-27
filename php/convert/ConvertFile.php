<?php
require_once "Logger.php";
require_once "request/Request.php";
require_once "OutputFile.php";
require_once "functions.php";
require_once "convert/ConvertSubtitle.php";
require_once "convert/ConvertAudio.php";
require_once "ffmpeg/FFmpegHelper.php";

class ConvertFile
{

    private $inputFilename = NULL;

    public $title = NULL;

    public $subtitle = NULL;

    public $season = NULL;

    public $episode = NULL;

    public $year = NULL;

    public $oRequest = NULL;

    public function __construct($inputFilename, $title=NULL, $year=NULL, $season=NULL, $episode=NULL, $subtitle=NULL)
    {
        $this->inputFilename = $inputFilename;
        $this->title = $title;
        $this->year = $year;
        $this->season = $season;
        $this->episode = $episode;
        $this->subtitle = $subtitle;
        $this->oRequest = Request::newInstanceFromEnv($this->inputFilename);
    }

    public function convert($oRequest)
    {
        Logger::info("Starting conversion for {}", $this->inputFilename);
        $oOutput = new OutputFile(getEnvWithDefault("APPLY_POSTFIX", "true") == "true" ? basename($this->inputFilename) : NULL); // use inputfile as the postfix only if APPLY_POSTFIX is set
        $oOutput->title = $this->title;
        $oOutput->subtitle = $this->subtitle;
        $oOutput->season = $this->season;
        $oOutput->episode = $this->episode;
        $oOutput->year = $this->year;

        Logger::verbose("Conversion output {}", $oOutput);
        Logger::verbose("Request information {}", $this->oRequest);
        $allRequests[] = $this->oRequest;
        $allRequests = array_merge($allRequests, ConvertAudio::convert($this->oRequest));
        $allRequests = array_merge($allRequests, ConvertSubtitle::convert($this->oRequest, $oOutput));

        $returnValue = FFmpegHelper::execute($allRequests, $oOutput, FALSE);
        Logger::info("Completed conversion.");
        return $returnValue;
    }
}

?>
