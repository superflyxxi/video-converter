<?php
require_once "InputFile.php";
require_once "functions.php";

class Request
{
    public $title = NULL;

    public $year = NULL;

    public $season = NULL;

    public $episode = NULL;

    public $subtitle = NULL;

    public $oInputFile = NULL;

    public $playlist = NULL;

    private $subtitleTracks = array(
        "*"
    );

    public $subtitleFormat = NULL;

    public $subtitleConversionBlacklist = NULL;

    public $subtitleConversionOutput = NULL;

    private $audioTracks = array(
        "*"
    );

    public $audioFormat = NULL;

    public $audioQuality = NULL;

    public $audioChannelLayout = NULL;

    private $audioChannelLayoutTracks = array();

    public $audioSampleRate = NULL;

    public $normalizeAudioTracks = NULL;

    private $hwaccel = false;

    public $deinterlace = false;

    public $deinterlaceMode = "02";

    private $videoTracks = array(
        "*"
    );

    public $videoFormat = NULL;

    private $videoHdr = false;

    public function __construct($filename)
    {
        $this->oInputFile = new InputFile($filename);
        $this->hwaccel = is_dir("/dev/dri");
        $this->videoHdr = getEnvWithDefault("HDR", "false") == "true";
    }

    public static function newInstanceFromEnv($filename)
    {
        $req = new Request($filename);

        $req->title = getEnv("TITLE");
        $req->year = getEnv("YEAR");
        $req->season = getEnv("SEASON");
        $req->episode = getEnv("EPISODE");
        $req->subtitle = getEnv("SUBTITLE");

        $req->playlist = getEnvWithDefault("PLAYLIST", NULL);
        $req->setSubtitleTracks(getEnvWithDefault("SUBTITLE_TRACKS", "*"));
        $req->subtitleFormat = getEnvWithDefault("SUBTITLE_FORMAT", "ass");
        $req->subtitleConversionOutput = getEnvWithDefault("SUBTITLE_CONVERSION_OUTPUT", "MERGE");
        $req->subtitleConversionBlacklist = getEnvWIthDefault("SUBTITLE_CONVERSION_BLACKLIST", "|\\~/\`_");

        $req->setAudioTracks(getEnvWithDefault("AUDIO_TRACKS", "*"));
        $req->audioFormat = getEnvWithDefault("AUDIO_FORMAT", "aac");
        $req->audioQuality = getEnvWithDefault("AUDIO_QUALITY", "2");
        $req->audioSampleRate = getEnvWithDefault("AUDIO_SAMPLE_RATE", NULL);
	$req->setNormalizeAudioTracks(getEnvWithDefault("NORMALIZE_AUDIO_TRACKS", ""));;
        $req->audioChannelLayout = getEnvWithDefault("AUDIO_CHANNEL_LAYOUT", "");
        $req->setAudioChannelLayoutTracks(getEnvWithDefault("AUDIO_CHANNEL_LAYOUT_TRACKS", "*"));

        $req->setVideoTracks(getEnvWithDefault("VIDEO_TRACKS", "*"));
        $req->videoFormat = getEnvWithDefault("VIDEO_FORMAT", "notcopy");
        $req->setDeinterlace(getEnvWithDefault("DEINTERLACE", NULL));
        $req->deinterlaceMode = getEnvWithDefault("DEINTERLACE_MODE", $req->deinterlaceMode);

        $req->prepareStreams();
        return $req;
    }
    
    private function setTracks($req)
    {
        return ($req === NULL || trim($req) == "") ? array() : explode(' ', $req);
    }

    private function areAllTracksConsidered($tracks)
    {
        return in_array("*", $tracks);
    }

    public function setAudioChannelLayoutTracks($req)
    {
        $this->audioChannelLayoutTracks = $this->setTracks($req);
    }

    public function areAllAudioChannelLayoutTracksConsidered()
    {
        return $this->areAllTracksConsidered($this->audioChannelLayoutTracks);
    }

    public function getAudioChannelLayoutTracks()
    {
        return $this->audioChannelLayoutTracks;
    }

    public function setAudioTracks($req)
    {
        $this->audioTracks = $this->setTracks($req);
    }

    public function areAllAudioTracksConsidered()
    {
        return $this->areAllTracksConsidered($this->audioTracks);
    }

    public function getAudioTracks()
    {
        return $this->audioTracks;
    }

    public function setNormalizeAudioTracks($req) {
        $this->normalizeAudioTracks = $this->setTracks($req);
    }

    public function setVideoTracks($req)
    {
        $this->videoTracks = $this->setTracks($req);
    }

    public function areAllVideoTracksConsidered()
    {
        return $this->areAllTracksConsidered($this->videoTracks);
    }

    public function getVideoTracks()
    {
        return $this->videoTracks;
    }

    public function setSubtitleTracks($req)
    {
        $this->subtitleTracks = $this->setTracks($req);
    }

    public function areAllSubtitleTracksConsidered()
    {
        return $this->areAllTracksConsidered($this->subtitleTracks);
    }

    public function getSubtitleTracks()
    {
        return $this->subtitleTracks;
    }

    public function setDeinterlace($val) {
        $this->deinterlace = $val;
        if ($this->deinterlace != NULL) {
            $this->deinterlace = ($this->deinterlace == "true");
        } else if ("copy" != $this->videoFormat) {
            $this->deinterlace = FFmpegHelper::isInterlaced($this->oInputFile) ? TRUE : FALSE;
        } else {
            $this->deinterlace = FALSE;
        }
    }

    public function prepareStreams()
    {
        Logger::debug("Preparing streams for {}.", $this->oInputFile->getFileName());
        if (! $this->areAllSubtitleTracksConsidered()) {
            Logger::debug("Not considering all subtitle streams. Requesting {}", $this->getSubtitleTracks());
            // if not * (all subtitles), then remove all track except the desired
            foreach ($this->oInputFile->getSubtitleStreams() as $track) {
                if (! in_array($track->index, $this->getSubtitleTracks())) {
                    Logger::debug("Removing subtitle track {} from input.", $track->index);
                    $this->oInputFile->removeSubtitleStream($track->index);
                } else {
                    Logger::debug("Keeping subtitle track {} in input.", $track->index);
                }
            }
        }
        if (! $this->areAllAudioTracksConsidered()) {
            Logger::debug("Not considering all audio streams. Requesting {}", $this->getAudioTracks());
            // if not * (all audio), then remove all track except the desired
            foreach ($this->oInputFile->getAudioStreams() as $track) {
                if (! in_array($track->index, $this->getAudioTracks())) {
                    Logger::debug("Removing audio track {} from input.", $track->index);
                    $this->oInputFile->removeAudioStream($track->index);
                } else {
                    Logger::debug("Keeping audio track {} in input.", $track->index);
                }
            }
        }
        if (! $this->areAllVideoTracksConsidered()) {
            Logger::debug("Not considering all video streams. Requesting {}", $this->getVideoTracks());
            // if not * (all videos), then remove all track except the desired
            foreach ($this->oInputFile->getVideoStreams() as $track) {
                if (! in_array($track->index, $this->getVideoTracks())) {
                    Logger::debug("Removing video track {} from input.", $track->index);
                    $this->oInputFile->removeVideoStream($track->index);
                } else {
                    Logger::debug("Keeping video track {} in input.", $track->index);
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

}

?>

