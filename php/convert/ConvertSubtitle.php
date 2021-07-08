<?php
require_once "request/Request.php";
require_once "InputFile.php";
require_once "functions.php";
require_once "LogWrapper.php";
require_once "ffmpeg/FFmpegHelper.php";
require_once "MKVExtractHelper.php";
require_once "exceptions/ExecutionException.php";
require_once "CountryToLanguageMapping.php";

class ConvertSubtitle
{
	public static $log;

    public static function convert($oRequest, $oOutput)
    {
        $dir = getEnvWithDefault("TMP_DIR", "/tmp");
        $arrAdditionalRequests = array();
        if ($oRequest->subtitleFormat != "copy") {
            $filename = $oRequest->oInputFile->getFileName();
            foreach ($oRequest->oInputFile->getSubtitleStreams() as $index => $subtitle) {
                $codecName = $subtitle->codec_name;
                $dvdFile = NULL;
		try {
                    if ("hdmv_pgs_subtitle" == $codecName) {
                        // convert to dvd
                        if ($oRequest->oInputFile->getPrefix() != NULL) {
                            $dvdFile = $dir . "/" . realpath($filename) . "/dir-" . $index;
                        } else {
                            $dvdFile = $dir . "/" . $filename . '-' . $index;
                        }
                        $pgsFile = new OutputFile(NULL, $dvdFile . '.sup');
                        if (! file_exists($pgsFile->getFileName())) {
                            self::$log->info("Generating PGS sup file for file.", array('index'=>$index, 'filename'=>$filename));
                            $pgsRequest = new Request($filename);
                            $pgsRequest->setSubtitleTracks($index);
                            $pgsRequest->setAudioTracks(NULL);
                            $pgsRequest->setVideoTracks(NULL);
                            $pgsRequest->subtitleFormat = "copy";
                            $pgsRequest->prepareStreams();
                            FFmpegHelper::execute(array($pgsRequest), $pgsFile, FALSE);
                        }

                        if (! file_exists($dvdFile . '.sub')) {
                            self::$log->info("Converting pgs to dvd subtitle.");
                            $command = 'java -jar /home/ripvideo/BDSup2Sub.jar -o "' . $dvdFile . '.sub" "' . $pgsFile->getFileName() . '"';
                            self::$log->debug("Executing command", array('command'=>$command));
                            exec($command, $out, $return);
                            if ($return != 0) {
                                throw new ExecutionException("java", $return, $command);
                            }
                        }
                    } else if ("vobsub" == $codecName || "dvd_subtitle" == $codecName) {
                        // extract vobsub
                        if ($oRequest->oInputFile->getPrefix() != NULL) {
                            $dvdFile = $dir . "/" . realpath($filename) . "/dir-" . $index;
                        } else {
                            $dvdFile = $dir . "/" . $filename . '-' . $index;
                        }
                        if (! file_exists($dvdFile . ".sub")) {
                            self::$log->info("Generating DVD sub file.", array('index'=>$index, 'filename'=>$filename));
                            $arrOutput = array($index => $dvdFile . ".sub");
                            MKVExtractHelper::extractTracks($oRequest->oInputFile, $arrOutput);
                        }
                    } else if ("subrip" == $codecName) {
                        self::$log->info("Adding subrip to request", array('index'=>$index, 'filename'=>$oRequest->oInputFile->getFileName()));
                        $oNewRequest = new Request($oRequest->oInputFile->getFileName());
                        $oNewRequest->setSubtitleTracks($index);
                        $oNewRequest->subtitleFormat = $oRequest->subtitleFormat;
                        $oNewRequest->setAudioTracks(NULL);
                        $oNewRequest->setVideoTracks(NULL);
                        $oNewRequest->prepareStreams();
                        $arrAdditionalRequests[] = $oNewRequest;
                        $oRequest->oInputFile->removeSubtitleStream($index);
                    }

                    // convert to srt
                    if (NULL != $dvdFile) {
                        if (! file_exists($dvdFile . ".srt")) {
                            self::$log->info("Convert DVD sub to SRT.");
                            $command = 'vobsub2srt ';
			    if (self::$log->isHandling(Logger::DEBUG)) {
				$command .= ' --verbose';
			    }
                            if (isset($subtitle->language)) {
                                $command .= ' --tesseract-lang ' . CountryToLanguageMapping::getCountry($subtitle->language) . ' ';
                            }
                            if (NULL != $oRequest->subtitleConversionBlacklist) {
                                $command .= " --blacklist '" . $oRequest->subtitleConversionBlacklist . "'";
                            }
                            $command .= ' "' . $dvdFile . '" ';
                            self::$log->debug("Using command", array('command'=>$command));
                            exec($command, $out, $return);
                            if ($return != 0) {
                                throw new ExecutionException("vobsub2srt", $return, $command);
                            }
                        }
                        if ($oRequest->subtitleConversionOutput == "MERGE") {
                            self::$log->info("Merging srt into mkv.", array('dvdfile'=>$dvdFile));
                            $oNewRequest = new Request($dvdFile . ".srt");
                            $oNewRequest->setSubtitleTracks("0");
                            $oNewRequest->setAudioTracks(NULL);
                            $oNewRequest->setVideoTracks(NULL);
                            $oNewRequest->subtitleFormat = $oRequest->subtitleFormat;
                            $oNewRequest->prepareStreams();
                            $oNewRequest->oInputFile->getSubtitleStreams()[0]->language = $subtitle->language;
                            self::$log->debug("Using language for final stream.", array('language'=>$subtitle->language));
                            $arrAdditionalRequests[] = $oNewRequest;
                        } else {
                            $newFile = $oOutput->getFileName() . "." . $index . "-" . $subtitle->language . ".srt";
                            self::$log->info("Keeping file outside", array('dvdfile'=>$dvdFile, 'newfile'=>$newFile));
                            rename($dvdFile . ".srt", $newFile);
                        }
                        $oRequest->oInputFile->removeSubtitleStream($index);
                    }
                } catch (ExecutionException $ex) {
	            self::$log->warn("Skipping track due to error", array('errorMessage'=>$ex->getMessage()));
                }
            }
            // if for some reason some couldn't be converted, copy the ones in the main input file
            $oRequest->subtitleFormat = "copy";
        }
        return $arrAdditionalRequests;
    }
}

ConvertSubtitle::$log = new LogWrapper('ConvertSubtitle');
?>
