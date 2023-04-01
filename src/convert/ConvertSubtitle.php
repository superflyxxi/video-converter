<?php
use SuperFlyXXI\VideoConverter\LogWrapper;
use SuperFlyXXI\VideoConverter\CountryToLanguageMapping;
use SuperFlyXXI\VideoConverter\Exceptions\ExecutionException;
use SuperFlyXXI\VideoConverter\Output\OutputFile;
use SuperFlyXXI\VideoConverter\Helpers\MKVExtractHelper;

require_once "request/Request.php";
require_once "InputFile.php";
require_once "functions.php";
require_once "ffmpeg/FFmpegHelper.php";

class ConvertSubtitle
{
    public static $log;

    public static function convert($oRequest, $oOutput)
    {
        $dir = getEnvWithDefault("TMP_DIR", "/tmp");
        $arrAdditionalRequests = [];
        if ($oRequest->subtitleFormat != "copy") {
            $filename = $oRequest->oInputFile->getFileName();
            foreach ($oRequest->oInputFile->getSubtitleStreams() as $index => $subtitle) {
                $codecName = $subtitle->codec_name;
                $dvdFile = null;
                try {
                    if ("hdmv_pgs_subtitle" == $codecName) {
                        $dvdFile = self::convertBluraySubtitle($oRequest, $dir, $filename, $index);
                    } elseif ("vobsub" == $codecName || "dvd_subtitle" == $codecName) {
                        $dvdFile = self::convertDvdSubtitle($oRequest, $dir, $filename, $index);
                    } elseif ("subrip" == $codecName) {
                        self::$log->info(
                            "Adding subrip to request",
                            [
                                "index" => $index,
                                "filename" => $oRequest->oInputFile->getFileName()
                            ]
                        );
                        $oNewRequest = new Request($oRequest->oInputFile->getFileName());
                        $oNewRequest->setSubtitleTracks($index);
                        $oNewRequest->subtitleFormat = $oRequest->subtitleFormat;
                        $oNewRequest->setAudioTracks(null);
                        $oNewRequest->setVideoTracks(null);
                        $oNewRequest->prepareStreams();
                        $arrAdditionalRequests[] = $oNewRequest;
                        $oRequest->oInputFile->removeSubtitleStream($index);
                    }

                    // convert to srt
                    if (null != $dvdFile) {
                        $oNewRequest = self::convertSrtSubtitle($dvdFile, $subtitle, $oRequest, $index, $oOutput);
                        if (null != $oNewRequest) {
                            $arrAdditionalRequests[] = $oNewRequest;
                        }
                    }
                } catch (ExecutionException $ex) {
                    self::$log->warning("Skipping track due to error", [
                        "errorMessage" => $ex->getMessage()
                    ]);
                }
            }
            // if for some reason some couldn't be converted, copy the ones in the main input file
            $oRequest->subtitleFormat = "copy";
        }
        return $arrAdditionalRequests;
    }

    private static function convertBluraySubtitle($oRequest, $dir, $filename, $index)
    {
        // convert to dvd
        $dvdFile = $dir . DIRECTORY_SEPARATOR . $oRequest->oInputFile->getTemporaryFileNamePrefix() . $index;
        $pgsFile = new OutputFile(null, $dvdFile . ".sup");
        if (! file_exists($pgsFile->getFileName())) {
            self::$log->info("Generating PGS sup file for file.", [
                "index" => $index,
                "filename" => $filename
            ]);
            $pgsRequest = new Request($filename);
            $pgsRequest->setSubtitleTracks($index);
            $pgsRequest->setAudioTracks(null);
            $pgsRequest->setVideoTracks(null);
            $pgsRequest->subtitleFormat = "copy";
            $pgsRequest->prepareStreams();
            FFmpegHelper::execute([
                $pgsRequest
            ], $pgsFile, false);
        }

        if (! file_exists($dvdFile . ".sub")) {
            self::$log->info("Converting pgs to dvd subtitle.");
            $command = 'java -jar /opt/BDSup2Sub.jar -o "' . $dvdFile . '.sub" "' . $pgsFile->getFileName() . '"';
            self::$log->debug("Executing command", [
                "command" => $command
            ]);
            passthru($command . " 2>&1", $return);
            if ($return != 0) {
                throw new ExecutionException("java", $return, $command);
            }
        }
        return $dvdFile;
    }

    private static function convertDvdSubtitle($oRequest, $dir, $filename, $index)
    {
        // extract vobsub
        $dvdFile = $dir . DIRECTORY_SEPARATOR . $oRequest->oInputFile->getTemporaryFileNamePrefix() . $index;
        if (! file_exists($dvdFile . ".sub")) {
            self::$log->info("Generating DVD sub file.", [
                "index" => $index,
                "filename" => $filename
            ]);
            $arrOutput = [
                $index => $dvdFile . ".sub"
            ];
            MKVExtractHelper::extractTracks($oRequest->oInputFile, $arrOutput);
        }
        return $dvdFile;
    }

    private static function convertSrtSubtitle($dvdFile, $subtitle, $oRequest, $index, $oOutput)
    {
        $oNewRequest = null;
        if (! file_exists($dvdFile . ".srt")) {
            self::$log->info("Convert DVD sub to SRT.");
            $command = "vobsub2srt ";
            if (self::$log->isDebugEnabled()) {
                $command .= " --verbose";
            }
            if (isset($subtitle->language)) {
                $command .= " --tesseract-lang " . CountryToLanguageMapping::getCountry($subtitle->language) . " ";
            }
            if (null != $oRequest->subtitleConversionBlacklist) {
                $command .= " --blacklist '" . $oRequest->subtitleConversionBlacklist . "'";
            }
            $command .= ' "' . $dvdFile . '" ';
            self::$log->debug("Using command", [
                "command" => $command
            ]);
            exec($command, $out, $return);
            if ($return != 0) {
                throw new ExecutionException("vobsub2srt", $return, $command);
            }
        }
        if ($oRequest->subtitleConversionOutput == "MERGE") {
            self::$log->info("Merging srt into mkv.", [
                "dvdfile" => $dvdFile
            ]);
            $oNewRequest = new Request($dvdFile . ".srt");
            $oNewRequest->setSubtitleTracks("0");
            $oNewRequest->setAudioTracks(null);
            $oNewRequest->setVideoTracks(null);
            $oNewRequest->subtitleFormat = $oRequest->subtitleFormat;
            $oNewRequest->prepareStreams();
            $oNewRequest->oInputFile->getSubtitleStreams()[0]->language = $subtitle->language;
            self::$log->debug("Using language for final stream.", [
                "language" => $subtitle->language
            ]);
        } else {
            $newFile = $oOutput->getFileName() . "." . $index . "-" . $subtitle->language . ".srt";
            self::$log->info("Keeping file outside", [
                "dvdfile" => $dvdFile,
                "newfile" => $newFile
            ]);
            rename($dvdFile . ".srt", $newFile);
        }
        $oRequest->oInputFile->removeSubtitleStream($index);
        return $oNewRequest;
    }
}

ConvertSubtitle::$log = new LogWrapper("ConvertSubtitle");
