<?php
require_once "TestSetup.php";
use PHPUnit\Framework\Attributes\DataProvider;

final class DeinterlaceDetectionTest extends TestSetup
{
    public static function probeModes(): array
    {
        return [
            "probe" => [
                "check-probe"
            ],
            "idet" => [
                "check-idet"
            ]
    ];
    }

    #[DataProvider('probeModes')]
    public function testAutoDeinterlace($check): void
    {
        $this->getFile("dvd");

        $return = $this->ripvideo(
            "dvd.mkv",
            [
                "--deinterlace-mode" => "00",
                "--deinterlace" => $check,
                "--audio-tracks" => - 1,
                "--subtitle-tracks" => - 1,
                "--title" => "Test " . $check . " Auto Deinterlace",
                "--year" => 2019
            ],
            "1m"
        );

        // not validating return as it can be killed; test("ffmpeg code", 0, $return, $testOutput);
        // $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test " . $check . " Auto Deinterlace (2019).dvd.mkv.mkv");

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("hevc", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        // doesn't exist... test("Stream 0 field_order", "progressive", $probe["streams"][0]["field_order"], $testOutput);
        $this->assertEquals("24000/1001", $probe["streams"][0]["r_frame_rate"], "Stream 0 r_frame_rate");
        $this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
        $this->assertEquals("Test " . $check . " Auto Deinterlace", $probe["format"]["tags"]["title"], "Metadata title");
        $this->assertEquals("2019", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
        $this->assertArrayNotHasKey("SEASON", $probe["format"]["tags"], "Metadata SEASON");
        $this->assertArrayNotHasKey("EPISODE", $probe["format"]["tags"], "Metadata EPISODE");
        $this->assertArrayNotHasKey("SUBTITLE", $probe["format"]["tags"], "Metadata SUBTITLE");
    }
}
