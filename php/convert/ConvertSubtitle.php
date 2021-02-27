<?php
require_once "request/Request.php";
require_once "InputFile.php";
require_once "functions.php";
require_once "Logger.php";
require_once "ffmpeg/FFmpegHelper.php";
require_once "MKVExtractHelper.php";
require_once "exceptions/ExecutionException.php";

class ConvertSubtitle
{

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
                            Logger::info("Generating PGS sup file for index {} of file '{}'.", $index, $filename);
                            $pgsRequest = new Request($filename);
                            $pgsRequest->setSubtitleTracks($index);
                            $pgsRequest->setAudioTracks(NULL);
                            $pgsRequest->setVideoTracks(NULL);
                            $pgsRequest->subtitleFormat = "copy";
                            $pgsRequest->prepareStreams();
                            FFmpegHelper::execute(array($pgsRequest), $pgsFile, FALSE);
                        }

                        if (! file_exists($dvdFile . '.sub')) {
                            Logger::info("Converting pgs to dvd subtitle.");
                            $command = 'java -jar /home/ripvideo/BDSup2Sub.jar -o "' . $dvdFile . '.sub" "' . $pgsFile->getFileName() . '"';
                            Logger::debug("Command: {}", $command);
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
                            Logger::info("Generating DVD sub file for index {} of file {}.", $index, $filename);
                            $arrOutput = array($index => $dvdFile . ".sub");
                            MKVExtractHelper::extractTracks($oRequest->oInputFile, $arrOutput);
                        }
                    } else if ("subrip" == $codecName) {
                        Logger::info("Adding subrip to request for track {} of file {}", $index, $oRequest->oInputFile->getFileName());
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
                            Logger::info("Convert DVD sub to SRT.");
                            $command = 'vobsub2srt ';
                            if (isset($subtitle->language)) {
                                $command .= ' --tesseract-lang ' . $subtitle->language . ' ';
                            }
                            if (NULL != $oRequest->subtitleConversionBlacklist) {
                                $command .= " --blacklist '" . $oRequest->subtitleConversionBlacklist . "'";
                            }
                            $command .= ' "' . $dvdFile . '" ';
                            Logger::debug("Command: {}", $command);
                            exec($command, $out, $return);
                            if ($return != 0) {
                                throw new ExecutionException("vobsub2srt", $return, $command);
                            }
                        }
                        if ($oRequest->subtitleConversionOutput == "MERGE") {
                            Logger::info("Merging srt, {}.srt, into mkv.", $dvdFile);
                            $oNewRequest = new Request($dvdFile . ".srt");
                            $oNewRequest->setSubtitleTracks("0");
                            $oNewRequest->setAudioTracks(NULL);
                            $oNewRequest->setVideoTracks(NULL);
                            $oNewRequest->subtitleFormat = $oRequest->subtitleFormat;
                            $oNewRequest->prepareStreams();
                            $oNewRequest->oInputFile->getSubtitleStreams()[0]->language = $subtitle->language;
                            Logger::debug("Using {} language for final stream.", $subtitle->language);
                            $arrAdditionalRequests[] = $oNewRequest;
                        } else {
                            $newFile = $oOutput->getFileName() . "." . $index . "-" . $subtitle->language . ".srt";
                            Logger::info("Keeping file, {}.srt, outside as {}", $dvdFile, $newFile);
                            rename($dvdFile . ".srt", $newFile);
                        }
                        $oRequest->oInputFile->removeSubtitleStream($index);
                    }
                } catch (ExecutionException $ex) {
	            Logger::warn("Skipping track due to error {}", $ex->getMessage());
                }
            }
            // if for some reason some couldn't be converted, copy the ones in the main input file
            $oRequest->subtitleFormat = "copy";
        }
        return $arrAdditionalRequests;
    }
}

?>

