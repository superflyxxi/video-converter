<?php
require_once "common.php";

final class AudioTest extends Test
{
    public function testChannelMappingOverrideToLowerValue()
    {
        $this->getFile("dvd");

        $return = $this->ripvideo([
            "INPUT" => "dvd.mkv",
            "TITLE" => "Test Channel Mapping",
            "AUDIO_CHANNEL_LAYOUT" => "stereo",
            "AUDIO_CHANNEL_LAYOUT_TRACKS" => 1,
            "VIDEO_TRACKS" => -1,
            "SUBTITLE_TRACKS" => -1,
        ]);
        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test Channel Mapping.dvd.mkv.mkv");

        $this->assertEquals(
            "audio",
            $probe["streams"][0]["codec_type"],
            "Stream 0 codec_type"
        );
        $this->assertEquals(
            "aac",
            $probe["streams"][0]["codec_name"],
            "Steram 0 codec"
        );
        $this->assertEquals(
            "stereo",
            $probe["streams"][0]["channel_layout"],
            "Stream 0 channel_layout"
        );
        $this->assertEquals(
            2,
            $probe["streams"][0]["channels"],
            "Stream 0 channels"
        );
        $this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
        $this->assertEquals(
            "Test Channel Mapping",
            $probe["format"]["tags"]["title"],
            "Metadata title"
        );
    }

    public function testNormalizing()
    {
        $this->getFile("dvd");

        $return = $this->ripvideo([
            "INPUT" => "dvd.mkv",
            "TITLE" => "Test Normalize Track 1",
            "YEAR" => 2019,
            "NORMALIZE_AUDIO_TRACKS" => 1,
            "VIDEO_TRACKS" => -1,
            "SUBTITLE_TRACKS" => -1,
        ]);
        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test Normalize Track 1 (2019).dvd.mkv.mkv");

        $this->assertEquals(
            "audio",
            $probe["streams"][0]["codec_type"],
            "Stream 0 codec_type"
        );
        $this->assertEquals(
            "aac",
            $probe["streams"][0]["codec_name"],
            "Stream 0 codec"
        );
        $this->assertEquals(
            6,
            $probe["streams"][0]["channels"],
            "Stream 0 channels"
        );
        $this->assertEquals(
            "5.1",
            $probe["streams"][0]["channel_layout"],
            "Stream 0 channel_layout"
        );
        $this->assertEquals(
            "audio",
            $probe["streams"][1]["codec_type"],
            "Stream 1 codec_type"
        );
        $this->assertEquals(
            "aac",
            $probe["streams"][1]["codec_name"],
            "Stream 1 codec"
        );
        $this->assertEquals(
            "5.1",
            $probe["streams"][1]["channel_layout"],
            "Stream 1 channel_layout"
        );
        $this->assertEquals(
            6,
            $probe["streams"][1]["channels"],
            "Stream 1 channels"
        );
        $this->assertEquals(
            "Normalized eng 5.1",
            $probe["streams"][1]["tags"]["title"],
            "Stream 1 title"
        );
        $this->assertArrayNotHasKey(2, $probe["streams"], "Stream 2 exists");
        $this->assertEquals(
            "Test Normalize Track 1",
            $probe["format"]["tags"]["title"],
            "Metadata title"
        );
    }
}
?>
