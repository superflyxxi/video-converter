<?php
include_once "Logger.php";
include_once "functions.php";
include_once "Request.php";
include_once "Stream.php";
include_once "exceptions/ExecutionException.php";

class ConvertAudio
{

    public static function convert($oRequest)
    {
        $arrAdditionalRequests = array();
        if ("copy" != $oRequest->audioFormat && count($oRequest->normalizeAudioTracks)) {
            // only do this there are tracks to normalize
            $dir = getEnvWithDefault("TMP_DIR", "/tmp");
            // any track that is not needed, just copy it to its own file
            foreach ($oRequest->oInputFile->getAudioStreams() as $index => $stream) {
                // copy original always and add to list of additional requests
                Logger::info("Converting audio track {}:{}", $oRequest->oInputFile->getFileName(), $index);
                $tmpRequest = new Request($oRequest->oInputFile->getFileName());
                $tmpRequest->setVideoTracks(NULL);
                $tmpRequest->setAudioTracks($index);
                $tmpRequest->setSubtitleTracks(NULL);
                $tmpRequest->audioFormat = $oRequest->audioFormat;
                $tmpRequest->audioQuality = $oRequest->audioQuality;
                $tmpRequest->audioChannelLayout = $oRequest->audioChannelLayout;
                $tmpRequest->setAudioChannelLayoutTracks(implode(" ", $oRequest->getAudioChannelLayoutTracks()));
                $tmpRequest->prepareStreams();
                if ($oRequest->oInputFile->getPrefix() != NULL) {
                    $convOutFile = new OutputFile(NULL, $dir . realpath($oRequest->oInputFile->getFileName()) . '/dir-' . $index . '-conv.mkv');
                } else {
                    $convOutFile = new OutputFile(NULL, $dir . $oRequest->oInputFile->getFileName() . '-' . $index . '-conv.mkv');
                }
                FFmpegHelper::execute(array($tmpRequest), $convOutFile);
                $oNewRequest = new Request($convOutFile->getFileName());
                $oNewRequest->setVideoTracks(NULL);
                $oNewRequest->setSubtitleTracks(NULL);
                $oNewRequest->setAudioTracks("0");
                $oNewRequest->audioFormat = "copy";
                $oNewRequest->prepareStreams();
                $arrAdditionalRequests[] = $oNewRequest;
                $oRequest->oInputFile->removeAudioStream($index);

                if (in_array($index, $oRequest->normalizeAudioTracks)) {
                    $arrAdditionalRequests[] = self::normalize($oRequest, $index, $dir, $convOutFile->getFileName(), $stream);
                }
            }
        }
        return $arrAdditionalRequests;
    }

    private static function normalize($oRequest, $index, $dir, $inFileName, $stream)
    {
        // if the track is to be normalized, now let's normalize it and put it in
        Logger::info("Normalizing track {}:{}", $oRequest->oInputFile->getFileName(), $index);
        $command = 'ffmpeg -hide_banner -i "' . $inFileName . '" -map 0 -filter:a loudnorm=print_format=json -f null - 2>&1';
        Logger::debug("Measuring {}:{} with command: {}", $oRequest->oInputFile->getFileName(), $index, $command);
        exec($command, $out, $return);
        if ($return != 0) {
	    throw new ExecutionException("ffmpeg", $return, $command);
        }
        Logger::verbose($out);
        $out = implode(array_slice($out, - 12));
        $json = json_decode($out, true);

        $normFile = $dir . $oRequest->oInputFile->getFileName() . '-' . $index . '-norm.mkv';
        $normChannelMap = ($oRequest->areAllAudioChannelLayoutTracksConsidered() || in_array($index, $oRequest->getAudioChannelLayoutTracks())) ? $oRequest->audioChannelLayout : $stream->channel_layout;
        if (NULL == $normChannelMap) {
            $normChannelMap = $stream->channel_layout;
        }
        $normChannelMap = preg_replace("/\(.+\)/", '', $normChannelMap);

        $sampleRate = $oRequest->audioSampleRate;
        if (NULL == $sampleRate) {
            $sampleRate = $stream->audio_sample_rate;
        }

        $command = 'ffmpeg -i "' . $inFileName . '" -y -map 0';
        $command .= ' -filter:a "loudnorm=measured_I=' . $json["input_i"] . ':measured_TP=' . $json["input_tp"] . ':measured_LRA=' . $json["input_lra"] . ':measured_thresh=' . $json["input_thresh"];
        if (NULL != $normChannelMap) {
            $command .= ',channelmap=channel_layout=' . $normChannelMap;
        }
        $command .= '" ';
        $command .= ' -c:a ' . $oRequest->audioFormat;
        $command .= ' -q:a ' . $oRequest->audioQuality;
        if (NULL != $sampleRate) {
            $command .= ' -ar ' . $sampleRate;
        }
        $command .= ' -metadata:s:a:0 "title=Normalized ' . $stream->language . ' ' . $normChannelMap . '"';
        $command .= ' -f matroska "' . $normFile . '" 2>&1';

        Logger::debug("Normalizing {}:{} with command: {}", $oRequest->oInputFile->getFileName(), $index, $command);
        passthru($command, $return);
        if ($return != 0) {
	    throw new ExecutionException("ffmpeg", $return, $command);
        }
        $oNewRequest = new Request($normFile);
        $oNewRequest->setAudioTracks("0");
        $oNewRequest->setVideoTracks(NULL);
        $oNewRequest->setSubtitleTracks(NULL);
        $oNewRequest->audioFormat = "copy";
        return $oNewRequest;
    }
}

?>
