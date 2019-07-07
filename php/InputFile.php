<?php
include_once "Stream.php";

class InputFile
{

    public function __construct($filename)
    {
        $this->filename = $filename;
        if (is_dir($filename) || substr($filename, - strlen($filename)) === ".iso") {
            $this->prefix = "bluray:";
        }
        $command = 'ffprobe -v quiet -print_format json -show_format -show_streams "' . $this->getFileName() . '"';
        exec($command, $out);
        $json = json_decode(implode($out), true);
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

    private $streams = array();

    private $subtitleStreams = array();

    private $videoStreams = array();

    private $audioStreams = array();

    private $filename = NULL;

    private $prefix = NULL;

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

    public function getFileName()
    {
        return $this->prefix . $this->filename;
    }
}

?>
