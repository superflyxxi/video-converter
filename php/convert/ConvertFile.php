<?php
include_once "Logger.php";
include_once "request/Request.php";
include_once "OutputFile.php";
include_once "functions.php";
include_once "convert/ConvertSubtitle.php";
include_once "convert/ConvertAudio.php";
include_once "ffmpeg/FFmpegHelper.php";

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

    public function convert()
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
        Logger::info("Completed conversion with {} as a return value.", $returnValue);
	if ($returnValue == 0) {
	        Logger::info("Chowning new file to match existing file.");
        	chown($oOutput->getFileName(), fileowner($oRequest->oInputFile->getFileName()));
	        chgrp($oOutput->getFileName(), filegroup($oRequest->oInputFile->getFileName()));
        	chmod($oOutput->getFileName(), fileperms($oRequest->oInputFile->getFileName()));
	}
        return $returnValue;
    }
}

?>
