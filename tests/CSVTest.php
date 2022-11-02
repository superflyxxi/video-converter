<?php
/*
 * Tests when no input is given. It should product results for the file as expected.
 */
require_once "common.php";

final class CSVTest extends Test
{
    public function test_Running_Against_CSV()
    {
        $this->getFile("dvd");
        $return = $this->ripvideo("csvs/test.csv", []);

        $this->assertEquals(0, $return, "Exit status not expected");

        $probe = $this->probe("CSV Request (2021) - s01e01 - Row 1.dvd.mkv.mkv", true);

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 code_type");
        $this->assertEquals("mpeg2video", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals("audio", $probe["streams"][1]["codec_type"], "Stream 1 codec type");
        $this->assertEquals("ac3", $probe["streams"][1]["codec_name"], "Stream 1 codec");
        $this->assertEquals("5.1(side)", $probe["streams"][1]["channel_layout"], "Stream 1 channel_layout");
        $this->assertEquals(6, $probe["streams"][1]["channels"], "Stream 1 channels");
        $this->assertEquals("subtitle", $probe["streams"][2]["codec_type"], "Stream 2 codec_type");
        $this->assertEquals("subrip", $probe["streams"][2]["codec_name"], "Stream 2 codec");
        $this->assertEquals("eng", $probe["streams"][2]["tags"]["language"], "Stream 2 language");
        $this->assertArrayNotHasKey(3, $probe["streams"], "Stream 3 exists");
        $this->assertEquals("CSV Request", $probe["format"]["tags"]["title"], "Metadata title");
        $this->assertEquals("2021", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
        $this->assertEquals("01", $probe["format"]["tags"]["SEASON"], "Metadata SEASON");
        $this->assertEquals("01", $probe["format"]["tags"]["EPISODE"], "Metadata EPISODE");
        $this->assertEquals("Row 1", $probe["format"]["tags"]["SHOWTITLE"], "Metadata SUBTITLE");

        $probe = $this->probe("CSV Request (2021) - s01e02 - Row 2.dvd.mkv.mkv", true);

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 code_type");
        $this->assertEquals("hevc", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals("audio", $probe["streams"][1]["codec_type"], "Stream 1 codec type");
        $this->assertEquals("eac3", $probe["streams"][1]["codec_name"], "Stream 1 codec");
        $this->assertEquals("stereo", $probe["streams"][1]["channel_layout"], "Stream 1 channel_layout");
        $this->assertEquals(2, $probe["streams"][1]["channels"], "Stream 1 channels");
        $this->assertEquals("audio", $probe["streams"][2]["codec_type"], "Stream 2 codec type");
        $this->assertEquals("eac3", $probe["streams"][2]["codec_name"], "Stream 2 codec");
        $this->assertEquals("stereo", $probe["streams"][2]["channel_layout"], "Stream 2 channel_layout");
        $this->assertEquals(2, $probe["streams"][2]["channels"], "Stream 2 channels");
        $this->assertEquals("subtitle", $probe["streams"][3]["codec_type"], "Stream 3 codec_type");
        $this->assertEquals("ass", $probe["streams"][3]["codec_name"], "Stream 3 codec");
        $this->assertEquals("eng", $probe["streams"][3]["tags"]["language"], "Stream 3 language");
        $this->assertArrayNotHasKey(4, $probe["streams"], "Stream 4 exists");
        $this->assertEquals("CSV Request", $probe["format"]["tags"]["title"], "Metadata title");
        $this->assertEquals("2021", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
        $this->assertEquals("01", $probe["format"]["tags"]["SEASON"], "Metadata SEASON");
        $this->assertEquals("02", $probe["format"]["tags"]["EPISODE"], "Metadata EPISODE");
        $this->assertEquals("Row 2", $probe["format"]["tags"]["SHOWTITLE"], "Metadata SUBTITLE");
    }
}
