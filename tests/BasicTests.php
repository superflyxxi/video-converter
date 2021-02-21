<?php
/*
 * Tests when no input is given. It should product results for the file as expected.
 */
include_once "common.php";

final class BasicTests extends Test
{

    public function testBlacklist() {
        $this->getFile("dvd");
	$this->ripvideo(array("TITLE"=>"Test No Input", "YEAR"=>2019, "AUDIO_FORMAT"=>"copy", "VIDEO_FORMAT"=>"copy", "SUBTITLE_FORMAT"=>"copy"), $output, $return);

        $this->assertEquals(0, $return, "Exit status not expected");

        $probe = $this->probe("Test No Input (2019).dvd.mkv.mkv", true);
        $probe = json_decode($probe, TRUE);
        print_r($probe);

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 code_type");
        $this->assertEquals("mpeg2video", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals("audio", $probe["streams"][1]["codec_type"], "Stream 1 codec type");
        $this->assertEquals("ac3", $probe["streams"][1]["codec_name"], "Stream 1 codec");
        $this->assertEquals("5.1(side)", $probe["streams"][1]["channel_layout"], "Stream 1 channel_layout");
        $this->assertEquals(6, $probe["streams"][1]["channels"], "Stream 1 channels");
        $this->assertEquals("subtitle", $probe["streams"][2]["codec_type"], "Stream 2 codec_type");
        $this->assertEquals("dvd_subtitle", $probe["streams"][2]["codec_name"], "Stream 2 codec");
        $this->assertFalse(array_key_exists(3, $probe["streams"]), "Stream 3 exists");
        $this->assertEquals("Test No Input", $probe["format"]["tags"]["title"], "Metadata title");
        $this->assertEquals("2019", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
        $this->assertFalse(array_key_exists("SEASON", $probe["format"]["tags"]), "Metadata SEASON exists");
        $this->assertFalse(array_key_exists("EPISODE", $probe["format"]["tags"]), "Metadata EPISODE exists");
        $this->assertFalse(array_key_exists("SUBTITLE", $probe["format"]["tags"]), "Metadata SUBTITLE exists");
    }
}
?>

