<?php
namespace SuperFlyXXI\VideoConverter\Input;

use Exception;
use SuperFlyXXI\VideoConverter\Helpers\FFmpegHelper;

class InputFile
{
    public function __construct($filename)
    {
        $this->filename = $filename ?? "";
        if (is_dir($filename) || substr($filename, - strlen($filename)) === ".iso") {
            $this->prefix = "bluray:";
        }
        $json = FFmpegHelper::probe($this);
        if ($json === false) {
            throw new Exception("Could not probe file " . $filename);
        }
        if (array_key_exists("streams", $json)) {
            foreach ($json["streams"] as $stream) {
                $oStream = new Stream($stream);
                $this->streams[$oStream->index] = $oStream;
                switch ($oStream->codec_type) {
                    case "video":
                        $this->videoStreams[$oStream->index] = $oStream;
                        break;
                    case "audio":
                        $this->audioStreams[$oStream->index] = $oStream;
                        break;
                    case "subtitle":
                        $this->subtitleStreams[$oStream->index] = $oStream;
                        break;
                    default:
                }
            }
        }
    }

    private $streams = [];

    private $subtitleStreams = [];

    private $videoStreams = [];

    private $audioStreams = [];

    private $filename = null;

    private $prefix = null;

    public function getSubtitleStreams()
    {
        return $this->subtitleStreams;
    }

    public function removeSubtitleStream($index)
    {
        unset($this->subtitleStreams[$index]);
        unset($this->streams[$index]);
    }

    public function getVideoStreams()
    {
        return $this->videoStreams;
    }

    public function removeVideoStream($index)
    {
        unset($this->videoStreams[$index]);
        unset($this->streams[$index]);
    }

    public function getAudioStreams()
    {
        return $this->audioStreams;
    }

    public function removeAudioStream($index)
    {
        unset($this->audioStreams[$index]);
        unset($this->streams[$index]);
    }

    public function getFileName(): string
    {
        return $this->filename;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getTemporaryFileNamePrefix()
    {
        $res = null != $this->prefix ? realpath($this->getFileName()) . "-dir-" : $this->getFileName() . "-";
        return str_replace(DIRECTORY_SEPARATOR, "-", $res ?? "");
    }
}
