<?php
namespace SuperFlyXXI\VideoConverter\Converters;

use SuperFlyXXI\VideoConverter\Options;
use SuperFlyXXI\VideoConverter\LogWrapper;
use SuperFlyXXI\VideoConverter\Helpers\FFmpegHelper;
use SuperFlyXXI\VideoConverter\Output\OutputFile;

class ConvertFile
{
    public static $log;

    private $inputFilename = null;

    private $req = null;

    public function __construct($req)
    {
        $this->req = $req;
        $this->inputFilename = $this->req->oInputFile->getFileName();
    }

    public function convert()
    {
        self::$log->info("Starting conversion for file", [
            "filename" => $this->inputFilename
        ]);
        // use inputfile as the postfix only if disable-postfix is not set
        $oOutput = new OutputFile(Options::get("disable-postfix") ? null : basename($this->inputFilename));
        $oOutput->title = $this->req->title;
        $oOutput->showTitle = $this->req->showTitle;
        $oOutput->season = $this->req->season;
        $oOutput->episode = $this->req->episode;
        $oOutput->year = $this->req->year;

        if (null == $this->req->title) {
            self::$log->error("title missing");
            return 1;
        }

        self::$log->debug("Conversion output", [
            "request" => $this->req,
            "output" => $oOutput
        ]);
        $allRequests[] = $this->req;
        $allRequests = array_merge($allRequests, ConvertVideo::convert($this->req));
        $allRequests = array_merge($allRequests, ConvertAudio::convert($this->req));
        $allRequests = array_merge($allRequests, ConvertSubtitle::convert($this->req, $oOutput));

        $returnValue = FFmpegHelper::execute($allRequests, $oOutput, false);
        self::$log->info("Completed conversion.");
        return $returnValue;
    }
}

ConvertFile::$log = new LogWrapper("ConvertFile");
