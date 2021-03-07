<?php
/*
 * Tests when no input is given. It should product results for the file as expected.
 */
require_once "common.php";

final class BasicTests extends Test
{
    public function testNoInputSpecifiedWithOnlyOneFile() {
        $this->getFile("dvd");
	$return = $this->ripvideo(array("TITLE"=>"Test No Input", "YEAR"=>2019, "AUDIO_FORMAT"=>"copy", "VIDEO_FORMAT"=>"copy", "SUBTITLE_FORMAT"=>"copy"));

        $this->assertEquals(0, $return, "Exit status not expected");

        $probe = $this->probe("Test No Input (2019).dvd.mkv.mkv", true);

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 code_type");
        $this->assertEquals("mpeg2video", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals("audio", $probe["streams"][1]["codec_type"], "Stream 1 codec type");
        $this->assertEquals("ac3", $probe["streams"][1]["codec_name"], "Stream 1 codec");
        $this->assertEquals("5.1(side)", $probe["streams"][1]["channel_layout"], "Stream 1 channel_layout");
        $this->assertEquals(6, $probe["streams"][1]["channels"], "Stream 1 channels");
        $this->assertEquals("subtitle", $probe["streams"][2]["codec_type"], "Stream 2 codec_type");
        $this->assertEquals("dvd_subtitle", $probe["streams"][2]["codec_name"], "Stream 2 codec");
        $this->assertEquals("eng", $probe["streams"][2]["tags"]["language"], "Stream 2 language");
        $this->assertEquals("subtitle", $probe["streams"][3]["codec_type"], "Stream 3 codec_type");
        $this->assertEquals("dvd_subtitle", $probe["streams"][3]["codec_name"], "Stream 3 codec");
        $this->assertEquals("fre", $probe["streams"][3]["tags"]["language"], "Stream 3 language");
        $this->assertArrayNotHasKey(4, $probe["streams"], "Stream 4 exists");
        $this->assertEquals("Test No Input", $probe["format"]["tags"]["title"], "Metadata title");
        $this->assertEquals("2019", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
        $this->assertArrayNotHasKey("SEASON", $probe["format"]["tags"], "Metadata SEASON exists");
        $this->assertArrayNotHasKey("EPISODE", $probe["format"]["tags"], "Metadata EPISODE exists");
        $this->assertArrayNotHasKey("SUBTITLE", $probe["format"]["tags"], "Metadata SUBTITLE exists");
    }

    public function testInputWithCopy() {
        $this->getFile("dvd");
        $return = $this->ripvideo(array("INPUT"=>"dvd.mkv", "TITLE"=>"Test Input", "YEAR"=>2019, "AUDIO_FORMAT"=>"copy", "VIDEO_FORMAT"=>"copy", "SUBTITLE_FORMAT"=>"copy"));
        $this->assertEquals(0, $return, "ffmpeg exit code");

        $probe = $this->probe("Test Input (2019).dvd.mkv.mkv");

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("mpeg2video", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals("audio", $probe["streams"][1]["codec_type"], "Stream 1 codec_type");
        $this->assertEquals("ac3", $probe["streams"][1]["codec_name"], "Stream 1 codec");
        $this->assertEquals("5.1(side)", $probe["streams"][1]["channel_layout"], "Stream 1 channel_layout");
        $this->assertEquals(6, $probe["streams"][1]["channels"], "Stream 1 channels");
        $this->assertEquals("subtitle", $probe["streams"][2]["codec_type"], "Stream 2 coded_type");
        $this->assertEquals("dvd_subtitle", $probe["streams"][2]["codec_name"], "Stream 2 codec");
        $this->assertEquals("subtitle", $probe["streams"][3]["codec_type"], "Stream 3 codec_type");
        $this->assertEquals("dvd_subtitle", $probe["streams"][3]["codec_name"], "Stream 3 codec");
        $this->assertArrayNotHasKey(4, $probe["streams"], "Stream 4 exists");
        $this->assertEquals("Test Input", $probe["format"]["tags"]["title"], "Metadata title");
        $this->assertEquals("2019", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
        $this->assertArrayNotHasKey("SEASON", $probe["format"]["tags"], "Metadata SEASON exists");
        $this->assertArrayNotHasKey("EPISODE", $probe["format"]["tags"], "Metadata EPISODE exists");
        $this->assertArrayNotHasKey("SUBTITLE", $probe["format"]["tags"], "Metadata SUBTITLE exists");
    }

    public function testTvShowMetadata() {
        $this->getFile("dvd");

        $return = $this->ripvideo(array("INPUT"=>"dvd.mkv", "TITLE"=>"Test tv show", "YEAR"=>2019, "SEASON"=>"01", "EPISODE"=>"23", "SUBTITLE"=>"The One Where Things", "VIDEO_FORMAT"=>"copy", "AUDIO_FORMAT"=>"copy", "SUBTITLE_FORMAT"=>"copy"));
        $this->assertEquals(0, $return, "rip-video exit code");

        $probe = $this->probe("Test tv show (2019) - s01e23 - The One Where Things.dvd.mkv.mkv");

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("mpeg2video", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals("audio", $probe["streams"][1]["codec_type"], "Stream 1 codec_type");
        $this->assertEquals("ac3", $probe["streams"][1]["codec_name"], "Steram 1 codec");
        $this->assertEquals("5.1(side)", $probe["streams"][1]["channel_layout"], "Stream 1 channel_layout");
        $this->assertEquals(6, $probe["streams"][1]["channels"], "Stream 1 channels");
        $this->assertEquals("subtitle", $probe["streams"][2]["codec_type"], "Stream 2 codec_type");
        $this->assertEquals("dvd_subtitle", $probe["streams"][2]["codec_name"], "Stream 2 codec");
        $this->assertEquals("subtitle", $probe["streams"][3]["codec_type"], "Stream 3 codec_type");
        $this->assertEquals("dvd_subtitle", $probe["streams"][3]["codec_name"], "Stream 3 codec");
        $this->assertArrayNotHasKey(4, $probe["streams"], "Stream 4 exists");
        $this->assertEquals("Test tv show", $probe["format"]["tags"]["title"], "Metadata title");
        $this->assertEquals("2019", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
        $this->assertEquals("01", $probe["format"]["tags"]["SEASON"], "Metadata SEASON");
        $this->assertEquals("23", $probe["format"]["tags"]["EPISODE"], "Metadata EPISODE");
        $this->assertEquals("The One Where Things", $probe["format"]["tags"]["SUBTITLE"], "Metadata SUBTITLE");
    }

    public function testNotApplyingPostfix() {
        $this->getFile("dvd");

        $return = $this->ripvideo(array("APPLY_POSTFIX"=>"false", "INPUT"=>"dvd.mkv", "TITLE"=>"Test Not Applying Postfix", "YEAR"=>2019, "VIDEO_FORMAT"=>"copy", "AUDIO_TRACKS"=>-1, "SUBTITLE_TRACKS"=>-1));
        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test Not Applying Postfix (2019).mkv");

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("mpeg2video", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
        $this->assertEquals("Test Not Applying Postfix", $probe["format"]["tags"]["title"], "Metadata title");
        $this->assertEquals("2019", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
        $this->assertArrayNotHasKey("SEASON", $probe["format"]["tags"], "Metadata SEASON");
        $this->assertArrayNotHasKey("EPISODE", $probe["format"]["tags"], "Metadata EPISODE");
        $this->assertArrayNotHasKey("SUBTITLE", $probe["format"]["tags"], "Metadata SUBTITLE");
    }
}
?>
