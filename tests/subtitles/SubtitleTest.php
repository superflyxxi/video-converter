<?php
require_once "common.php";

final class SubtitleTests extends Test
{

    public function testDvdSubtitleConversion() {
        $this->getFile("dvd");

        $return = $this->ripvideo(array("APPLY_POSTFIX"=>"false", "INPUT"=>"dvd.mkv", "TITLE"=>"Test Convert DVD Subtitle", "VIDEO_TRACKS"=>-1, "AUDIO_TRACKS"=>-1, "SUBTITLE_FORMAT"=>"srt", "YEAR"=>2019));

        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test Convert DVD Subtitle (2019).mkv");

        $this->assertEquals("subtitle", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("subrip", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals("eng", $probe["streams"][0]["tags"]["language"], "Stream 0 language");
        $this->assertFalse(array_key_exists(1, $probe["streams"]), "Stream 1 exists");
        $this->assertEquals("Test Convert DVD Subtitle", $probe["format"]["tags"]["title"], "Metadata title");
    }

    public function testBlacklist() {
        $this->markTestIncomplete("Blacklist doesn't work the way you think it should.");
        $this->getFile("dvd");

        $return = $this->ripvideo(array("APPLY_POSTFIX"=>"false", "INPUT"=>"dvd.mkv", "TITLE"=>"Test Subtitle Files", "VIDEO_FORMAT"=>"copy", "AUDIO_TRACKS"=>-1, "SUBTITLE_FORMAT"=>"srt", "SUBTITLE_CONVERSION_OUTPUT"=>"FILE", "SUBTITLE_CONVERSION_BLACKLIST"=>"’!\�~@~", "YEAR"=>2019));

        $this->assertEquals(0, $return, "ripvideo exit code"); //test("ffmpeg code", 0, $return, $output);

        $probe = $this->probe("Test Subtitle Files (2019).mkv");

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0");
	$this->assertFalse(array_key_exists(1, $probe["streams"]), "Stream 1 exists");
        $this->assertEquals("Test Subtitle Files", $probe["format"]["tags"]["title"], "Metadata Title");
        $this->assertEquals("2019", $probe["format"]["tags"]["YEAR"], "Metadata Year");

        $testfile = $this->getDataDir() . DIRECTORY_SEPARATOR . "Test Subtitle Files (2019).mkv.2-eng.srt";
        $this->assertFileExists($testfile, "File missing");
	$contents = file_get_contents($testfile);
	$this->assertFalse(strpos($contents, "’"), "SRT contains ’");
	$this->assertFalse(strpos($contents, "!"), "SRT contains |");
    }

    public function testBluraySubtitles() {
        $this->getFile("bluray.mkv");

        $return = $this->ripvideo(array("APPLY_POSTFIX"=>"false", "INPUT"=>"bluray.mkv", "TITLE"=>"Test Convert Bluray Subtitle", "VIDEO_TRACKS"=>-1, "AUDIO_TRACKS"=>-1, "YEAR"=>2019));

        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test Convert Bluray Subtitle (2019).mkv");

        $this->assertEquals("subtitle", $probe["streams"][0]["codec_type"], "Stream 0 code_type");
        $this->assertEquals("ass", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals("eng", $probe["streams"][0]["tags"]["language"], "Stream 0 language");
        $this->assertFalse(array_key_exists(1, $probe["streams"]), "Stream 1 exists");
        $this->assertEquals("Test Convert Bluray Subtitle", $probe["format"]["tags"]["title"], "Metadata title");
    }
}
?>
