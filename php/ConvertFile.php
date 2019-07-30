<?php
include_once "Logger.php";
include_once "Request.php";
include_once "OutputFile.php";
include_once "functions.php";
include_once "SubtitleConvert.php";
include_once "NormalizeAudio.php";
include_once "FFmpegHelper.php";

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

    public function convert()
    {
        Logger::info("Starting conversion for {}", array(
            $this->inputFilename
        ));
        $oOutput = new OutputFile(basename($this->inputFilename)); // use inputfile as the postfix
        $oOutput->title = $this->title;
        $oOutput->subtitle = $this->subtitle;
        $oOutput->season = $this->season;
        $oOutput->episode = $this->episode;
        $oOutput->year = $this->year;

        $oRequest = Request::newInstanceFromEnv($this->inputFilename);
        $allRequests[] = $oRequest;
        $allRequests = array_merge($allRequests, NormalizeAudio::normalize($oRequest));
        $allRequests = array_merge($allRequests, SubtitleConvert::convert($oRequest));

        $returnValue = FFmpegHelper::execute($allRequests, $oOutput);
        Logger::info("Completed conversion with {} as a return value.", array(
            $returnValue
        ));

        Logger::info("Chowning new file to match existing file");
        chown($oOutput->getFileName(), fileowner($oRequest->oInputFile->getFileName()));
        chgrp($oOutput->getFileName(), filegroup($oRequest->oInputFile->getFileName()));
        chmod($oOutput->getFileName(), fileperms($oRequest->oInputFile->getFileName()));
        return $returnValue;
    }
}

?>
