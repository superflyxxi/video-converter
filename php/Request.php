<?php
include_once "InputFile.php";
include_once "functions.php";

class Request
{

    public function __construct($filename)
    {
        $this->oInputFile = new InputFile($filename);
        
        $this->hwaccel = is_dir("/dev/dri");
        $this->videoHdr = getEnvWithDefault("HDR", "false") == "true";
        $this->playlist = getEnv("PLAYLIST");
        $this->subtitleTrack = getEnvWithDefault("SUBTITLE_TRACK", "s?");
        $this->subtitleFormat = getEnvWithDefault("SUBTITLE_FORMAT", "ass");
        
        $this->audioTrack = getEnvWithDefault("AUDIO_TRACK", "a");
        $this->audioFormat = getEnvWithDefault("AUDIO_FORMAT", "aac");
        $this->audioQuality = getEnvWithDefault("AUDIO_QUALITY", "2");
        $this->normalizeAudioTracks = explode(" ", getEnvWIthDefault("NORMALIZE_AUDIO_TRACKS", ""));
        $mapping = getEnvWithDefault("AUDIO_CHANNEL_LAYOUT", "5.1");
        foreach (explode(" ", getEnvWithDefault("AUDIO_CHANNEL_MAPPING_TRACKS", "1")) as $track) {
            $this->audioChannelMapping[$track] = $mapping;
        }
        
        $this->deinterlace = ("true" == getEnvWithDefault("DEINTERLACE", "false"));
        
        $this->videoTrack = getEnvWithDefault("VIDEO_TRACK", "v");
        $this->videoFormat = getEnvWithDefault("VIDEO_FORMAT", "notcopy");
        
        $this->prepareStreams();
    }

    private function prepareStreams()
    {
        if (substr($this->subtitleTrack, 0, strlen("s")) !== "s") {
            // if not s (all subtitles), then remove all track except the desired
            foreach ($this->oInputFile->getSubtitleStreams() as $track) {
                if ($this->subtitleTrack != $track->index) {
                    $this->oInputFile->removeSubtitleStream($track->index);
                }
            }
        }
        if (substr($this->audioTrack, 0, strlen("a")) !== "a") {
            // if not a (all audio), then remove all track except the desired
            foreach ($this->oInputFile->getAudioStreams() as $track) {
                if ($this->audioTrack != $track->index) {
                    $this->oInputFile->removeAudioStream($track->index);
                }
            }
        }
        if (substr($this->videoTrack, 0, strlen("v")) !== "v") {
            // if not s (all subtitles), then remove all track except the desired
            foreach ($this->oInputFile->getVideoStreams() as $track) {
                if ($this->videoTrack != $track->index) {
                    $this->oInputFile->removeVideoStream($track->index);
                }
            }
        }
    }

    public function isHwaccel()
    {
        return $this->hwaccel;
    }

    public function isHDR()
    {
        return $this->videoHdr;
    }

    public $oInputFile = NULL;

    public $playlist = NULL;

    public $subtitleTrack = NULL;

    public $subtitleFormat = NULL;

    public $audioFormat = NULL;

    public $audioTrack = NULL;

    public $audioQuality = NULL;

    public $audioChannelMapping = NULL;

    public $normalizeAudioTracks = NULL;

    private $hwaccel = false;

    public $deinterlace = false;

    public $videoTrack = NULL;

    public $videoFormat = NULL;

    private $videoHdr = false;
}

?>
