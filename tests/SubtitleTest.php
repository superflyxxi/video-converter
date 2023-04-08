<?php
require_once "Test.php";

final class SubtitleTest extends Test
{
    public function sourceFormats(): array
    {
        return [
            /* cover by testSrtToAss
            "dvdSubripEng" => [
                "dvd",
                "2",
                "subrip",
                "eng"
            ],*/
            "dvdAssFre" => [
                "dvd",
                "3",
                "ass",
                "fre"
            ],
            "blurayAssEng" => [
                "bluray",
                "2",
                "ass",
                "eng"
            ]
        ];
    }

    /**
     *
     * @test
     * @dataProvider sourceFormats
     */
    public function testSubtitleConversion($source, $track, $format, $language)
    {
        $this->getFile($source);

        $return = $this->ripvideo(
            $source . ".mkv",
            [
                "--disable-postfix" => true,
                "--title" => "Test Convert $source-$track Subtitle to $format",
                "--video-tracks" => - 1,
                "--audio-tracks" => - 1,
                "--subtitle-tracks" => $track,
                "--subtitle-format" => $format,
                "--year" => 2019
            ]
        );

        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test Convert $source-$track Subtitle to $format (2019).mkv");

        $this->assertEquals("subtitle", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals($format, $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals($language, $probe["streams"][0]["tags"]["language"], "Stream 0 language");
        $this->assertArrayNotHasKey(1, $probe["streams"], "Stream 2 exists");
        $this->assertEquals(
            "Test Convert $source-$track Subtitle to $format",
            $probe["format"]["tags"]["title"],
            "Metadata title"
        );
    }

    public function testBlacklist()
    {
        $this->markTestIncomplete("Blacklist doesn't work the way you think it should.");
        $this->getFile("dvd");

        $return = $this->ripvideo(
            "dvd.mkv",
            [
                "--disable-postfix" => true,
                "--title" => "Test Subtitle Files",
                "--video-format" => "copy",
                "--audio-tracks" => - 1,
                "--subtitle-format" => "srt",
                "--subtitle-conversion-output" => "FILE",
                "--subtitle-conversion-blacklist" => "’!\�~@~",
                "--year" => 2019
            ]
        );

        $this->assertEquals(0, $return, "ripvideo exit code"); // test("ffmpeg code", 0, $return, $output);

        $probe = $this->probe("Test Subtitle Files (2019).mkv");

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0");
        $this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
        $this->assertEquals("Test Subtitle Files", $probe["format"]["tags"]["title"], "Metadata Title");
        $this->assertEquals("2019", $probe["format"]["tags"]["YEAR"], "Metadata Year");

        $testfile = $this->getDataDir() . DIRECTORY_SEPARATOR . "Test Subtitle Files (2019).mkv.2-eng.srt";
        $this->assertFileExists($testfile, "File for 2-eng missing");
        $contents = file_get_contents($testfile);
        $this->assertFalse(strpos($contents, "’"), "SRT contains ’");
        $this->assertFalse(strpos($contents, "!"), "SRT contains |");

        $testfile = $this->getDataDir() . DIRECTORY_SEPARATOR . "Test Subtitle Files (2019).mkv.3-fre.srt";
        $this->assertFileExists($testfile, "File for 3-fre missing");
    }

    public function testSrtToAss()
    {
        $track ="2";
        $this->testSubtitleConversion("dvd", $track, "subrip", "eng");

        $return = $this->ripvideo(
            "Test Convert dvd-$track Subtitle to subrip (2019).mkv",
            [
                "--disable-postfix" => true,
                "--title" => "Test Convert to Ass",
                "--video-tracks" => - 1,
                "--audio-tracks" => - 1,
                "--subtitle-tracks" => $track,
                "--subtitle-format" => "ass",
                "--year" => 2019
            ]
        );

        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test Convert to Ass (2019).mkv");

        $this->assertEquals("subtitle", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("ass", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals("eng", $probe["streams"][0]["tags"]["language"], "Stream 0 language");
        $this->assertArrayNotHasKey(1, $probe["streams"], "Stream 2 exists");
    }
}
