<?php
include_once "Request.php";
include_once "InputFile.php";
include_once "functions.php";
include_once "Logger.php";
include_once "ffmpeg/FFmpegHelper.php";
include_once "MKVExtractHelper.php";

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
                        if (FFmpegHelper::execute(array(
                            $pgsRequest
                        ), $pgsFile, FALSE) > 0) {
                            Logger::warn("Conversion failed... Skipping this stream.");
                            continue;
                        }
                    }

                    if (! file_exists($dvdFile . '.sub')) {
                        Logger::info("Converting pgs to dvd subtitle.");
                        $command = 'java -jar /home/ripvideo/BDSup2Sub.jar -o "' . $dvdFile . '.sub" "' . $pgsFile->getFileName() . '"';
                        Logger::debug("Command: {}", $command);
                        exec($command, $out, $return);
                        if ($return != 0) {
                            Logger::warn("sub convertion failed: {}. Continuing with next subtitle.", $return);
                            continue;
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
                        $arrOutput = array(
                            $index => $dvdFile . ".sub"
                        );
                        if (MKVExtractHelper::extractTracks($oRequest->oInputFile, $arrOutput) > 0) {
                            Logger::warn("Conversion failed... Skipping this stream.");
                            continue;
                        }
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
                        $command .= ' "' . $dvdFile . '" ';
                        Logger::debug("Command: {}", $command);
                        exec($command, $out, $return);
                        if ($return != 0) {
                            Logger::warn("vobsub to srt conversion failed: {}. Continuing with next stream.", $return);
                            continue;
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
			rename($dvdFile, $newFile);
                    }
                    $oRequest->oInputFile->removeSubtitleStream($index);
                }
            }
            // if for some reason some couldn't be converted, copy the ones in the main input file
            $oRequest->subtitleFormat = "copy";
        }
        return $arrAdditionalRequests;
    }
}

?>
