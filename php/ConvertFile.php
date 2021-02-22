<?php
include_once "Logger.php";
include_once "Request.php";
include_once "OutputFile.php";
include_once "functions.php";
include_once "ConvertSubtitle.php";
include_once "ConvertAudio.php";
include_once "ffmpeg/FFmpegHelper.php";

class ConvertFile
{

    private $inputFilename = NULL;

    private $title = NULL;

    private $subtitle = NULL;

    private $season = NULL;

    private $episode = NULL;

    private $year = NULL;

    public function __construct($inputFilename, $title, $year, $season, $episode, $subtitle)
    {
        $this->inputFilename = $inputFilename;
        $this->title = $title;
        $this->year = $year;
        $this->season = $season;
        $this->episode = $episode;
        $this->subtitle = $subtitle;
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
        Logger::verbose("Request information {}", $oRequest);
        $allRequests[] = $oRequest;
        $allRequests = array_merge($allRequests, ConvertAudio::convert($oRequest));
        $allRequests = array_merge($allRequests, ConvertSubtitle::convert($oRequest, $oOutput));

        $returnValue = FFmpegHelper::execute($allRequests, $oOutput, FALSE);
        Logger::info("Completed conversion.");
        return $returnValue;
    }
}

?>
