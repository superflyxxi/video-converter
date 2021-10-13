<?php
require_once "common.php";

final class SimpleScaleTest extends Test
{
    public function test_Simple_Upscaling()
    {
        $this->getFile("dvd");

        $return = $this->ripvideo(
            [
                "INPUT" => "dvd.mkv",
                "TITLE" => "Test 1.5x Simple Upscale",
                "VIDEO_UPSCALE" => "1.5",
                "AUDIO_TRACKS" => -1,
                "SUBTITLE_TRACKS" => -1,
                "DEINTERLACE" => "false",
            ],
            "10m"
        );
        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test 1.5x Simple Upscale.dvd.mkv.mkv");

        $this->assertEquals(
            "video",
            $probe["streams"][0]["codec_type"],
            "Stream 0 codec_type"
        );
        $this->assertEquals(
            "hevc",
            $probe["streams"][0]["codec_name"],
            "Stream 0 codec"
        );
        $this->assertEquals(
            "720",
            $probe["streams"][0]["height"],
            "Stream 0 height"
        );
        $this->assertEquals(
            "1080",
            $probe["streams"][0]["width"],
            "Stream 0 width"
        );
        $this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
        $this->assertEquals(
            "Test 1.5x Simple Upscale",
            $probe["format"]["tags"]["title"],
            "Metadata title"
        );
    }

    public function test_Simple_Downscaling()
    {
        $this->getFile("dvd");

        $return = $this->ripvideo([
            "INPUT" => "dvd.mkv",
            "TITLE" => "Test 0.5x Downscale",
            "VIDEO_UPSCALE" => "0.5",
            "AUDIO_TRACKS" => -1,
            "SUBTITLE_TRACKS" => -1,
            "DEINTERLACE" => "false",
        ]);
        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test 0.5x Downscale.dvd.mkv.mkv");

        $this->assertEquals(
            "video",
            $probe["streams"][0]["codec_type"],
            "Stream 0 codec_type"
        );
        $this->assertEquals(
            "hevc",
            $probe["streams"][0]["codec_name"],
            "Stream 0 codec"
        );
        $this->assertEquals(
            "240",
            $probe["streams"][0]["height"],
            "Stream 0 height"
        );
        $this->assertEquals(
            "360",
            $probe["streams"][0]["width"],
            "Stream 0 width"
        );
        $this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
        $this->assertEquals(
            "Test 0.5x Downscale",
            $probe["format"]["tags"]["title"],
            "Metadata title"
        );
    }
}
?>
