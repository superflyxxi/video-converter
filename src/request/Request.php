<?php
require_once "LogWrapper.php";
require_once "InputFile.php";
require_once "functions.php";
require_once "Options.php";

class Request
{
    public static $log;

    public $title = null;

    public $year = null;

    public $season = null;

    public $episode = null;

    public $showTitle = null;

    public $oInputFile = null;

    public $playlist = null;

    private $subtitleTracks = [
        "*"
    ];

    public $subtitleFormat = null;

    public $subtitleConversionBlacklist = null;

    public $subtitleConversionOutput = null;

    private $audioTracks = [
        "*"
    ];

    public $audioFormat = null;

    public $audioQuality = null;

    public $audioChannelLayout = null;

    private $audioChannelLayoutTracks = [];

    public $audioSampleRate = null;

    public $normalizeAudioTracks = null;

    private $hwaccel = false;

    public $deinterlace = false;

    public $deinterlaceMode = "02";

    private $videoTracks = [
        "*"
    ];

    public $videoFormat = null;

    private $videoHdr = false;

    public $videoUpscale = 1;

    public function __construct($filename)
    {
        $this->oInputFile = new InputFile($filename);
        $this->hwaccel = is_dir("/dev/dri");
        $this->videoHdr = Options::get("hdr");
    }

    public static function newInstanceFromEnv($filename)
    {
        $req = new Request($filename);

        $req->title = Options::get("title");
        $req->year = Options::get("year");
        $req->season = Options::get("season");
        $req->episode = Options::get("episode");
        $req->showTitle = Options::get("show-title");

        $req->playlist = Options::get("PLAYLIST", null);

        $req->setVideoTracks(Options::get("video-tracks", "*"));
        $req->videoFormat = Options::get("video-format", "libx265");
        $req->videoUpscale = Options::get("video-upscale", 1);
        $req->setDeinterlace(Options::get("deinterlace", "off"));

        $req->setAudioTracks(Options::get("audio-tracks", "*"));
        $req->audioFormat = Options::get("audio-format", "aac");
        $req->audioQuality = Options::get("audio-quality", "2");
        $req->audioSampleRate = Options::get("audio-sample-rate", null);
        $req->setNormalizeAudioTracks(Options::get("normalize-audio-tracks", ""));
        $req->audioChannelLayout = Options::get("audio-channel-layout", "");
        $req->setAudioChannelLayoutTracks(Options::get("audio-channel-layout-tracks", "*"));

        $req->setSubtitleTracks(Options::get("subtitle-tracks", "*"));
        $req->subtitleFormat = Options::get("subtitle-format", "ass");
        $req->subtitleConversionOutput = Options::get("subtitle-conversion-output", "MERGE");
        $req->subtitleConversionBlacklist = Options::get("subtitle-conversion-blacklist", "|\\~/\`_");

        $req->prepareStreams();
        return $req;
    }

    private function setTracks($req)
    {
        return $req === null || trim($req) == "" ? [] : explode(" ", $req);
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

    public function setNormalizeAudioTracks($req)
    {
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

    public function setDeinterlace($val)
    {
        $this->deinterlace = $val;
        if ("off" != $this->deinterlace && "copy" != $this->videoFormat) {
            $this->deinterlace = FFmpegHelper::isInterlaced($this->oInputFile);
            $this->deinterlaceMode = $val;
        } else {
            $this->deinterlace = false;
            $this->deinterlaceMode = null;
        }
    }

    public function prepareStreams()
    {
        self::$log->debug("Preparing streams.", [
            "filename" => $this->oInputFile->getFileName()
        ]);
        $this->prepareSubtitleStreams();
        $this->prepareAudioStreams();
        $this->prepareVideoStreams();
    }

    private function prepareSubtitleStreams()
    {
        if (! $this->areAllSubtitleTracksConsidered()) {
            self::$log->debug(
                "Not considering all subtitle streams.",
                [
                    "subtitleTracks" => $this->getSubtitleTracks()
                ]
            );
            // if not * (all subtitles), then remove all track except the desired
            foreach ($this->oInputFile->getSubtitleStreams() as $track) {
                if (! in_array($track->index, $this->getSubtitleTracks())) {
                    self::$log->debug("Removing subtitle track from input.", [
                        "index" => $track->index
                    ]);
                    $this->oInputFile->removeSubtitleStream($track->index);
                } else {
                    self::$log->debug("Keeping subtitle track in input.", [
                        "index" => $track->index
                    ]);
                }
            }
        }
    }

    private function prepareAudioStreams()
    {
        if (! $this->areAllAudioTracksConsidered()) {
            self::$log->debug("Not considering all audio streams.", [
                "audioTracks" => $this->getAudioTracks()
            ]);
            // if not * (all audio), then remove all track except the desired
            foreach ($this->oInputFile->getAudioStreams() as $track) {
                if (! in_array($track->index, $this->getAudioTracks())) {
                    self::$log->debug("Removing audio track from input.", [
                        "index" => $track->index
                    ]);
                    $this->oInputFile->removeAudioStream($track->index);
                } else {
                    self::$log->debug("Keeping audio track in input.", [
                        "index" => $track->index
                    ]);
                }
            }
        }
    }

    private function prepareVideoStreams()
    {
        if (! $this->areAllVideoTracksConsidered()) {
            self::$log->debug("Not considering all video streams.", [
                "videoTracks" => $this->getVideoTracks()
            ]);
            // if not * (all videos), then remove all track except the desired
            foreach ($this->oInputFile->getVideoStreams() as $track) {
                if (! in_array($track->index, $this->getVideoTracks())) {
                    self::$log->debug("Removing video track from input.", [
                        "index" => $track->index
                    ]);
                    $this->oInputFile->removeVideoStream($track->index);
                } else {
                    self::$log->debug("Keeping video track in input.", [
                        "index" => $track->index
                    ]);
                }
            }
        }
    }

    public function isHwAccelDecode()
    {
        return $this->hwaccel;
    }

    public function isHwAccelEncode()
    {
        return $this->hwaccel && strpos($this->videoFormat, "vaapi");
    }

    public function isHDR()
    {
        return $this->videoHdr;
    }
}

Request::$log = new LogWrapper("Request");
