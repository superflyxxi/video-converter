<?php
use SuperFlyXXI\VideoConverter\Input\InputFile;

require_once "Test.php";

/*
 * Tests when no input is given. It should product results for the file as expected.
 */
final class StreamTest extends Test
{
    public function test_DVD_Video_Stream()
    {
        $this->getFile("dvd");
        $file = new InputFile($this->getDataDir() . DIRECTORY_SEPARATOR . "dvd.mkv");
        $videoStreams = $file->getVideoStreams();
        $this->assertEquals(1, count($videoStreams), "Video Streams");
        $this->assertEquals("video", $videoStreams[0]->codec_type, "Codec type");
        $this->assertEquals("mpeg2video", $videoStreams[0]->codec_name, "Codec name");
        $this->assertEquals(0, $videoStreams[0]->index, "Index");
        $this->assertEquals("30000/1001", $videoStreams[0]->frame_rate, "Frame rate");
        $this->assertEquals("720", $videoStreams[0]->width, "Width");
        $this->assertEquals("480", $videoStreams[0]->height, "Height");
        $this->assertEquals("eng", $videoStreams[0]->language, "Language");
        $this->assertEquals(null, $videoStreams[0]->channel_layout, "Channel layout");
        $this->assertEquals(null, $videoStreams[0]->channels, "Channels");
        $this->assertEquals(null, $videoStreams[0]->audio_sample_rate, "Audio sample rate");
    }

    public function test_DVD_Audio_Stream()
    {
        $this->getFile("dvd");
        $file = new InputFile($this->getDataDir() . DIRECTORY_SEPARATOR . "dvd.mkv");
        $audioStreams = $file->getAudioStreams();
        $this->assertEquals(1, count($audioStreams), "Audio Streams");
        $this->assertEquals("audio", $audioStreams[1]->codec_type, "Codec type");
        $this->assertEquals("ac3", $audioStreams[1]->codec_name, "Codec name");
        $this->assertEquals(1, $audioStreams[1]->index, "Index");
        $this->assertEquals("5.1(side)", $audioStreams[1]->channel_layout, "Channel layout");
        $this->assertEquals(6, $audioStreams[1]->channels, "Channels");
        $this->assertEquals(null, $audioStreams[1]->audio_sample_rate, "Audio sample rate");
        $this->assertEquals(null, $audioStreams[1]->width, "Width");
        $this->assertEquals(null, $audioStreams[1]->height, "Height");
        $this->assertEquals("eng", $audioStreams[1]->language, "Language");
        $this->assertEquals("0/0", $audioStreams[1]->frame_rate, "Frame rate");
    }

    public function test_DVD_Subtitle_Stream()
    {
        $this->getFile("dvd");
        $file = new InputFile($this->getDataDir() . DIRECTORY_SEPARATOR . "dvd.mkv");
        $subStreams = $file->getSubtitleStreams();
        $this->assertEquals(2, count($subStreams), "Audio Streams");
        $this->assertEquals("subtitle", $subStreams[2]->codec_type, "2. Codec type");
        $this->assertEquals("dvd_subtitle", $subStreams[2]->codec_name, "2. Codec name");
        $this->assertEquals(2, $subStreams[2]->index, "Index");
        $this->assertEquals("eng", $subStreams[2]->language, "2. Language");
        $this->assertEquals(null, $subStreams[2]->channel_layout, "2. Channel layout");
        $this->assertEquals(null, $subStreams[2]->channels, "2. Channels");
        $this->assertEquals(720, $subStreams[2]->width, "2. Width");
        $this->assertEquals(480, $subStreams[2]->height, "2. Height");
        $this->assertEquals(null, $subStreams[2]->audio_sample_rate, "2. Audio sample rate");
        $this->assertEquals("0/0", $subStreams[2]->frame_rate, "2. Frame rate");
        $this->assertEquals("subtitle", $subStreams[3]->codec_type, "3. Codec type");
        $this->assertEquals("dvd_subtitle", $subStreams[3]->codec_name, "3. Codec name");
        $this->assertEquals(3, $subStreams[3]->index, "Index");
        $this->assertEquals("fre", $subStreams[3]->language, "3. Language");
        $this->assertEquals(null, $subStreams[3]->channel_layout, "3. Channel layout");
        $this->assertEquals(null, $subStreams[3]->channels, "3. Channels");
        $this->assertEquals(720, $subStreams[3]->width, "3. Width");
        $this->assertEquals(480, $subStreams[3]->height, "3. Height");
        $this->assertEquals(null, $subStreams[3]->audio_sample_rate, "3. Audio sample rate");
        $this->assertEquals("0/0", $subStreams[3]->frame_rate, "3. Frame rate");
    }
}
