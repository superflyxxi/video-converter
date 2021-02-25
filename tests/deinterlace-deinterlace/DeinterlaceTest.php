<?php
require_once "common.php";

final class DeinterlacesTests extends Test
{

    public function testDeinterlaceAndMode00() {
        $this->getFile("dvd");

        $return = $this->ripvideo(array("INPUT"=>"dvd.mkv", "DEINTERLACE"=>"true", "DEINTERLACE_MODE"=>"00", "AUDIO_TRACKS"=>-1, "SUBTITLE_TRACKS"=>-1, "TITLE"=>"Test Deinterlace Mode 00"));

// not validating return as it can be killed; test("ffmpeg code", 0, $return, $testOutput);
        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test Deinterlace Mode 00.dvd.mkv.mkv");

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("hevc", $probe["streams"][0]["codec_name"], "Stream 0 codec");
// doesn't exist... test("Stream 0 field_order", "progressive", $probe["streams"][0]["field_order"], $testOutput);
        $this->assertEquals("24000/1001", $probe["streams"][0]["r_frame_rate"], "Stream 0 r_frame_rate");
        $this->assertFalse(array_key_exists(1, $probe["streams"]), "Stream 1 exists");
        $this->assertEquals("Test Deinterlace Mode 00", $probe["format"]["tags"]["title"], "Metadata title");
    }

    public function testDeinterlaceMode01() {
        $this->getFile("dvd");

        $return = $this->ripvideo(array("INPUT"=>"dvd.mkv", "DEINTERLACE"=>"true", "DEINTERLACE_MODE"=>"01", "AUDIO_TRACKS"=>-1, "SUBTITLE_TRACKS"=>-1, "TITLE"=>"Test Deinterlace Mode 01", "YEAR"=>2021));

// not validating return as it can be killed; test("ffmpeg code", 0, $return, $testOutput);
        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test Deinterlace Mode 01 (2021).dvd.mkv.mkv");

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("hevc", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals("19001/317", $probe["streams"][0]["r_frame_rate"], "Stream 0 r_frame_rate");
        $this->assertFalse(array_key_exists(1, $probe["streams"]), "Stream 1 exists");
        $this->assertEquals("Test Deinterlace Mode 01", $probe["format"]["tags"]["title"], "Metadata title");
        $this->assertEquals("2021", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
    }

    public function testDeinterlaceMode02() {
        $this->getFile("dvd");

        $return = $this->ripvideo(array("INPUT"=>"dvd.mkv", "DEINTERLACE"=>"true", "DEINTERLACE_MODE"=>"02", "AUDIO_TRACKS"=>-1, "SUBTITLE_TRACKS"=>-1, "TITLE"=>"Test Deinterlace Mode 02", "YEAR"=>2021));

        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test Deinterlace Mode 02 (2021).dvd.mkv.mkv");

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("hevc", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals("30000/1001", $probe["streams"][0]["r_frame_rate"], "Stream 0 r_frame_rate");
        $this->assertFalse(array_key_exists(1, $probe["streams"]), "Stream 1 exists");
        $this->assertEquals("Test Deinterlace Mode 02", $probe["format"]["tags"]["title"], "Metadata title");
        $this->assertEquals("2021", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
    }
}
?>
