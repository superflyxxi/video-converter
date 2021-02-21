<?php
require_once "common.php";

final class SubtitleTests extends Test
{

    public function testBlacklist() {
        $this->getFile("dvd");

        $this->ripvideo(array("APPLY_POSTFIX"=>"false", "INPUT"=>"dvd.mkv", "TITLE"=>"Test Subtitle Files", "VIDEO_FORMAT"=>"copy", "AUDIO_TRACKS"=>-1, "SUBTITLE_FORMAT"=>"srt", "SUBTITLE_CONVERSION_OUTPUT"=>"FILE", "SUBTITLE_CONVERSION_BLACKLIST"=>"!\�~@~", "YEAR"=>2019), $output, $return);

        $this->assertEquals(0, $return, "ripvideo exit code"); //test("ffmpeg code", 0, $return, $output);

        $probe = $this->probe("Test Subtitle Files (2019).mkv");

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0");
	$this->assertFalse(array_key_exists(1, $probe["streams"]), "Stream 1 exists");
        $this->assertEquals("Test Subtitle Files", $probe["format"]["tags"]["title"], "Metadata Title");
        $this->assertEquals("2019", $probe["format"]["tags"]["YEAR"], "Metadata Year");

        $testfile = getEnv("TMP_DIR")."/Test Subtitle Files (2019).mkv.2-eng.srt";
        $this->assertFileExists($testfile, "File missing");
	$contents = file_get_contents($testfile);
	$this->assertFalse(strpos($contents, "’"), "SRT contains '");
	$this->assertFalse(strpos($contents, "!"), "SRT contains |");
    }
}
?>
