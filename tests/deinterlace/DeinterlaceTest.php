<?php
include_once "common.php";

final class DeinterlacesTests extends Test
{

    public function testProbeAutoDeinterlaceAndMode00() {
        $this->getFile("dvd");

        $this->ripvideo(array("INPUT"=>"dvd.mkv", "DEINTERLACE_MODE"=>"00", "AUDIO_TRACKS"=>-1, "SUBTITLE_TRACKS"=>-1, "TITLE"=>"Test Probe Auto Deinterlace", "YEAR"=>2019), $output, $return);

// not validating return as it can be killed; test("ffmpeg code", 0, $return, $testOutput);
        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test Probe Auto Deinterlace (2019).dvd.mkv.mkv");

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("hevc", $probe["streams"][0]["codec_name"], "Stream 0 codec");
// doesn't exist... test("Stream 0 field_order", "progressive", $probe["streams"][0]["field_order"], $testOutput);
        $this->assertEquals("24000/1001", $probe["streams"][0]["r_frame_rate"], "Stream 0 r_frame_rate");
        $this->assertFalse(array_key_exists(1, $probe["streams"]), "Stream 1 exists");
        $this->assertEquals("Test Probe Auto Deinterlace", $probe["format"]["tags"]["title"], "Metadata title");
        $this->assertEquals("2019", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
        $this->assertFalse(array_key_exists("SEASON", $probe["format"]["tags"]), "Metadata SEASON");
        $this->assertFalse(array_key_exists("EPISODE", $probe["format"]["tags"]), "Metadata EPISODE");
        $this->assertFalse(array_key_exists("SUBTITLE", $probe["format"]["tags"]), "Metadata SUBTITLE");
    }

    public testIdetAutoDeinterlaceMode00() {
        $this->getFile("dvd");

        $this->ripvideo(array("INPUT"=>"dvd.mkv", "DEINTERLACE_MODE"=>"00", "DEINTERLACE_CHECK"=>"idet", "AUDIO_TRACKS"=>-1, "SUBTITLE_TRACKS"=>-1, "TITLE"=>"Test Idet Auto Deinterlace", "YEAR"=>2021), $output, $return);

// not validating return as it can be killed; test("ffmpeg code", 0, $return, $testOutput);
        $this->assertEquals(0, $return, "ripvideo exit code");

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
