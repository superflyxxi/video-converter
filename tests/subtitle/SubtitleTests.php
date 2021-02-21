<?php
include_once "common.php";
use PHPUnit\Framework\TestCase;

final class SubtitleTests extends TestCase
{

    public function testBlacklist() {
        CommonTestUtil::getInstance()->getFile("dvd.mkv", "/samples/DVD_Sample.mkv");

        test_ffmpeg(array("APPLY_POSTFIX"=>"false", "INPUT"=>"dvd.mkv", "TITLE"=>"Test Subtitle Files", "VIDEO_FORMAT"=>"copy", "AUDIO_TRACKS"=>-1, "SUBTITLE_FORMAT"=>"srt", "SUBTITLE_CONVERSION_OUTPUT"=>"FILE", "SUBTITLE_CONVERSION_BLACKLIST"=>"!\�~@~", "YEAR"=>2019), $output, $return);

        $this->assertEquals(0, $return, "ffmpeg code"); //test("ffmpeg code", 0, $return, $output);

        $probe = probe("/data/Test Subtitle Files (2019).mkv");
        $probe = json_decode($probe, true);

        $testOutput = array($output, $probe);

        $this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0");
	$this->assertFalse(array_key_exists(1, $probe["streams"]), "Stream 1 exists");
        $this->assertEquals("Test Subtitle Files", $probe["format"]["tags"]["title"], "Metadata Title");
        $this->assertEquals("2019", $probe["format"]["tags"]["YEAR"], "Metadata Year");
        $this->assertFalse(array_key_exists("SEASON", $probe["format"]["tags"]), "Metadata Season exists");
        $this->assertFalse(array_key_exists("EPISODE", $probe["format"]["tags"]), "Metadata Episode exists");
        $this->assertFalse(array_key_exists("SUBTITLE", $probe["format"]["tags"]), "Metadata Subtitle exists");

        $testfile = getEnv("TMP_DIR")."/Test Subtitle Files (2019).mkv.2-eng.srt";
        $this->assertFileExists($testfile, "File missing");
	$contents = file_get_contents($testfile);
	$this->assertFalse(strpos($contents, "’"), "SRT contains '");
	$this->assertFalse(strpos($contents, "!"), "SRT contains |");
    }
}
?>
