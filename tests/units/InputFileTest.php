<?php
/*
 * Tests when no input is given. It should product results for the file as expected.
 */
require_once "common.php";
require_once "InputFile.php";

final class InputFileTests extends Test
{
	public function test_InputFile_Video_DVD() {
		$this->getFile("dvd");
		$file = new InputFile($this->getDataDir() . DIRECTORY_SEPARATOR . "dvd.mkv");
		$videoStreams = $file->getVideoStreams();
		$this->assertEquals(1, count($videoStreams), "Video Streams");
		$this->assertEquals("video", $videoStreams[0]->codec_type, "Codec type");
		$this->assertEquals("mpeg2video", $videoStreams[0]->codec_name, "Codec name");
		$this->assertEquals(0, $videoStreams[0]->index, "Index");
		$this->assertEquals("30000/1001", $videoStreams[0]->frame_rate, "Frame rate");
		$this->assertEquals("eng", $videoStreams[0]->language, "Language");
		$this->assertEquals(NULL, $videoStreams[0]->channel_layout, "Channel layout");
		$this->assertEquals(NULL, $videoStreams[0]->channels, "Channels");
		$this->assertEquals(NULL, $videoStreams[0]->audio_sample_rate, "Audio sample rate");
	}
	
	public function test_InputFile_Audio_DVD() {
		$this->getFile("dvd");
		$file = new InputFile($this->getDataDir() . DIRECTORY_SEPARATOR . "dvd.mkv");
		$audioStreams = $file->getAudioStreams();
		$this->assertEquals(1, count($audioStreams), "Audio Streams");
		$this->assertEquals("audio", $audioStreams[1]->codec_type, "Codec type");
		$this->assertEquals("ac3", $audioStreams[1]->codec_name, "Codec name");
		$this->assertEquals(1, $audioStreams[1]->index, "Index");
		$this->assertEquals('5.1(side)', $audioStreams[1]->channel_layout, "Channel layout");
		$this->assertEquals(6, $audioStreams[1]->channels, "Channels");
		$this->assertEquals(NULL, $audioStreams[1]->audio_sample_rate, "Audio sample rate");
		$this->assertEquals("eng", $audioStreams[1]->language, "Language");
		$this->assertEquals("0/0", $audioStreams[1]->frame_rate, "Frame rate");
	}
	
	public function test_InputFile_Subtitle_DVD() {
		$this->getFile("dvd");
		$file = new InputFile($this->getDataDir() . DIRECTORY_SEPARATOR . "dvd.mkv");
		$subStreams = $file->getSubtitleStreams();
		$this->assertEquals(2, count($subStreams), "Audio Streams");
		$this->markTestIncomplete("Missing correct assertions");
		$this->assertEquals("audio", $audioStreams[1]->codec_type, "Codec type");
		$this->assertEquals("ac3", $audioStreams[1]->codec_name, "Codec name");
		$this->assertEquals(1, $audioStreams[1]->index, "Index");
		$this->assertEquals('5.1(side)', $audioStreams[1]->channel_layout, "Channel layout");
		$this->assertEquals(6, $audioStreams[1]->channels, "Channels");
		$this->assertEquals(NULL, $audioStreams[1]->audio_sample_rate, "Audio sample rate");
		$this->assertEquals("eng", $audioStreams[1]->language, "Language");
		$this->assertEquals("0/0", $audioStreams[1]->frame_rate, "Frame rate");
	}
}
