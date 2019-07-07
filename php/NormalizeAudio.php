<?php
include_once "functions.php";
include_once "Request.php";
include_once "Stream.php";

class NormalizeAudio
{

    public static function normalize($oRequest)
    {
        $arrAdditionalRequests = array();
        if ("copy" != $oRequest->audioFormat) {
            $dir = getEnvWithDefault("TMP_DIR", "/tmp");
            foreach ($oRequest->normalizeAudioTracks as $index) {
                if (is_numeric($index)) {
                    $stream = $oRequest->oInputFile->getAudioStreams()[$index];
                    $origFile = $dir . $oRequest->oInputFile->getFileName() . '-' . $index . '-orig.mkv';
                    $command = 'ffmpeg -i "' . $oRequest->oInputFile->getFileName() . '" -map 0:' . $index . ' -c copy -f matroska "' . $origFile . '"';
                    printf("Copying origin %s with command: %s\n", $index, $command);
                    exec($command, $out, $return);
                    if ($return != 0) {
                        printf("Copying failed: %s\n", $return);
                        exit($return);
                    }
                    $oNewRequest = new Request($origFile);
                    $oNewRequest->audioTrack = 0;
                    $oNewRequest->normalizeAudioTracks = array();
                    $arrAdditionalRequests[] = $oNewRequest;
                    $oRequest->oInputFile->removeAudioStream($index);
                    
                    $command = 'ffmpeg -hide_banner -i "' . $origFile . '" -map 0 -filter:a loudnorm=print_format=json -f null - 2>&1';
                    printf("Measuring %s with command: %s\n", $index, $command);
                    exec($command, $out, $return);
                    if ($return != 0) {
                        printf("Normalizing failed: %s\n", $return);
                        exit($return);
                    }
                    $out = implode(array_slice($out, - 12));
                    $json = json_decode($out, true);
                    
                    $normFile = $dir . $oRequest->oInputFile->getFileName() . '-' . $index . '-norm.mkv';
                    $command = 'ffmpeg -i "' . $origFile . '" -y -map 0' . ' -filter:a "loudnorm=measured_I=' . $json["input_i"] . ':measured_TP=' . $json["input_tp"] . ':measured_LRA=' . $json["input_lra"] . ':measured_thresh=' . $json["input_thresh"] . (NULL != $stream->channel_layout ? ',channelmap=channel_layout=' . $stream->channel_layout : ' ') . '" ' . ' -c:a ' . $oRequest->audioFormat . ' -q:a ' . $oRequest->audioQuality . ' -metadata:s:a:0 "title=Normalized ' . $stream->language . ' ' . $stream->channel_layout . '"' . ' -f matroska "' . $normFile . '"';
                    
                    printf("Normalizing %s with command: %s\n", $index, $command);
                    exec($command, $out, $return);
                    if ($return != 0) {
                        printf("Normalizing failed: %s\n", $return);
                        exit($return);
                    }
                    $oNewRequest = new Request($normFile);
                    $oNewRequest->audioTrack = 0;
                    $oNewRequest->audioFormat = "copy";
                    $arrAdditionalRequests[] = $oNewRequest;
                }
            }
        }
        return $arrAdditionalRequests;
    }
}

?>
