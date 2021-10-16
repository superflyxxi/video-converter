<?php
require_once "common.php";

final class SubtitleTest extends Test {
	public function testDvdSubtitleConversion() {
		$this->getFile("dvd");

		$return = $this->ripvideo("dvd.mkv", [
			"--disable-postfix" => "false",
			"--title" => "Test Convert DVD Subtitle",
			"--video-tracks" => -1,
			"--audio-tracks" => -1,
			"--subtitle-format" => "srt",
			"--year" => 2019,
		]);

		$this->assertEquals(0, $return, "ripvideo exit code");

		$probe = $this->probe("Test Convert DVD Subtitle (2019).mkv");

		$this->assertEquals(
			"subtitle",
			$probe["streams"][0]["codec_type"],
			"Stream 0 codec_type"
		);
		$this->assertEquals(
			"subrip",
			$probe["streams"][0]["codec_name"],
			"Stream 0 codec"
		);
		$this->assertEquals(
			"eng",
			$probe["streams"][0]["tags"]["language"],
			"Stream 0 language"
		);
		$this->assertEquals(
			"subtitle",
			$probe["streams"][1]["codec_type"],
			"Stream 1 codec_type"
		);
		$this->assertEquals(
			"subrip",
			$probe["streams"][1]["codec_name"],
			"Stream 1 codec"
		);
		$this->assertEquals(
			"fre",
			$probe["streams"][1]["tags"]["language"],
			"Stream 1 language"
		);
		$this->assertArrayNotHasKey(2, $probe["streams"], "Stream 2 exists");
		$this->assertEquals(
			"Test Convert DVD Subtitle",
			$probe["format"]["tags"]["title"],
			"Metadata title"
		);
	}

	public function testBlacklist() {
		$this->markTestIncomplete(
			"Blacklist doesn't work the way you think it should."
		);
		$this->getFile("dvd");

		$return = $this->ripvideo("dvd.mkv",[
			"--disable-postfix" => "false",
			"--title" => "Test Subtitle Files",
			"--video-format" => "copy",
			"--audio-tracks" => -1,
			"--subtitle-format" => "srt",
			"--subtitle-conversion-output" => "FILE",
			"--subtitle-conversion-blacklist" => "’!\�~@~",
			"--year" => 2019,
		]);

		$this->assertEquals(0, $return, "ripvideo exit code"); //test("ffmpeg code", 0, $return, $output);

		$probe = $this->probe("Test Subtitle Files (2019).mkv");

		$this->assertEquals(
			"video",
			$probe["streams"][0]["codec_type"],
			"Stream 0"
		);
		$this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
		$this->assertEquals(
			"Test Subtitle Files",
			$probe["format"]["tags"]["title"],
			"Metadata Title"
		);
		$this->assertEquals(
			"2019",
			$probe["format"]["tags"]["YEAR"],
			"Metadata Year"
		);

		$testfile =
			$this->getDataDir() .
			DIRECTORY_SEPARATOR .
			"Test Subtitle Files (2019).mkv.2-eng.srt";
		$this->assertFileExists($testfile, "File for 2-eng missing");
		$contents = file_get_contents($testfile);
		$this->assertFalse(strpos($contents, "’"), "SRT contains ’");
		$this->assertFalse(strpos($contents, "!"), "SRT contains |");

		$testfile =
			$this->getDataDir() .
			DIRECTORY_SEPARATOR .
			"Test Subtitle Files (2019).mkv.3-fre.srt";
		$this->assertFileExists($testfile, "File for 3-fre missing");
	}

	public function testBluraySubtitles() {
		$this->getFile("bluray.mkv");

		$return = $this->ripvideo("bluray.mkv",[
			"--disable-postfix" => "false",
			"--title" => "Test Convert Bluray Subtitle",
			"--video-tracks" => -1,
			"--audio-tracks" => -1,
			"--year" => 2019,
		]);

		$this->assertEquals(0, $return, "ripvideo exit code");

		$probe = $this->probe("Test Convert Bluray Subtitle (2019).mkv");

		$this->assertEquals(
			"subtitle",
			$probe["streams"][0]["codec_type"],
			"Stream 0 code_type"
		);
		$this->assertEquals(
			"ass",
			$probe["streams"][0]["codec_name"],
			"Stream 0 codec"
		);
		$this->assertEquals(
			"eng",
			$probe["streams"][0]["tags"]["language"],
			"Stream 0 language"
		);
		$this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
		$this->assertEquals(
			"Test Convert Bluray Subtitle",
			$probe["format"]["tags"]["title"],
			"Metadata title"
		);
	}
}
?>
