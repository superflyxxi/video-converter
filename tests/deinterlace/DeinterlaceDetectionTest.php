<?php
require_once "common.php";

final class DeinterlaceDetectionTests extends Test
{

    public function testProbeAutoDeinterlace() {
        $this->getFile("dvd");

        $return = $this->ripvideo(array("INPUT"=>"dvd.mkv", "DEINTERLACE_MODE"=>"00", "AUDIO_TRACKS"=>-1, "SUBTITLE_TRACKS"=>-1, "TITLE"=>"Test Probe Auto Deinterlace", "YEAR"=>2019), "1m");

        $probe = $this->probe("Test Probe Auto Deinterlace (2019).dvd.mkv.mkv");

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("hevc", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals("24000/1001", $probe["streams"][0]["r_frame_rate"], "Stream 0 r_frame_rate");
        $this->assertFalse(array_key_exists(1, $probe["streams"]), "Stream 1 exists");
        $this->assertEquals("Test Probe Auto Deinterlace", $probe["format"]["tags"]["title"], "Metadata title");
        $this->assertEquals("2019", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
        $this->assertFalse(array_key_exists("SEASON", $probe["format"]["tags"]), "Metadata SEASON");
        $this->assertFalse(array_key_exists("EPISODE", $probe["format"]["tags"]), "Metadata EPISODE");
        $this->assertFalse(array_key_exists("SUBTITLE", $probe["format"]["tags"]), "Metadata SUBTITLE");
    }

    public function testIdetAutoDeinterlace() {
        $this->getFile("dvd");

        $return = $this->ripvideo(array("INPUT"=>"dvd.mkv", "DEINTERLACE_MODE"=>"00", "DEINTERLACE_CHECK"=>"idet", "AUDIO_TRACKS"=>-1, "SUBTITLE_TRACKS"=>-1, "TITLE"=>"Test Idet Auto Deinterlace", "YEAR"=>2021), "1m");

        $probe = $this->probe("Test Idet Auto Deinterlace (2021).dvd.mkv.mkv");

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("hevc", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals("24000/1001", $probe["streams"][0]["r_frame_rate"], "Stream 0 r_frame_rate");
        $this->assertFalse(array_key_exists(1, $probe["streams"]), "Stream 1 exists");
        $this->assertEquals("Test Idet Auto Deinterlace", $probe["format"]["tags"]["title"], "Metadata title");
        $this->assertEquals("2021", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
    }
}
?>
