<?php
include_once "common.php";

final class AudioTests extends Test
{

    public function testChannelMappingOverrideToLowerValue() {
        $this->getFile("dvd");

        $this->ripvideo(array("INPUT"=>"dvd.mkv", "TITLE"=>"Test Channel Mapping", "AUDIO_CHANNEL_LAYOUT"=>"stereo", "AUDIO_CHANNEL_LAYOUT_TRACKS"=>1, "VIDEO_TRACKS"=>-1, "SUBTITLE_TRACKS"=>-1), $output, $return);
        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test Channel Mapping (2019).dvd.mkv.mkv");

        $this->assertEquals("audio", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("aac", $probe["streams"][0]["codec_name"], "Steram 0 codec");
        $this->assertEquals("stereo", $probe["streams"][0]["channel_layout"], "Stream 0 channel_layout");
        $this->assertEquals(2, $probe["streams"][0]["channels"], "Stream 0 channels");
        $this->assertFalse(array_key_exists(1, $probe["streams"]), "Stream 1 exists");
        $this->assertEquals("Test Channel Mapping", $probe["format"]["tags"]["title"], "Metadata title");
    }
}
?>
