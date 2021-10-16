<?php
require_once "common.php";

final class ConvertVideoTest extends Test {
	public function test_x265_Conversion() {
		$this->getFile("dvd");

		$return = $this->ripvideo([
			"INPUT" => "dvd.mkv",
			"--title" => "Test x265 Conversion",
			"AUDIO_TRACKS" => -1,
			"SUBTITLE_TRACKS" => -1,
			"DEINTERLACE" => "false",
		]);
		$this->assertEquals(0, $return, "ripvideo exit code");

		$probe = $this->probe("Test x265 Conversion.dvd.mkv.mkv");

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
			"480",
			$probe["streams"][0]["height"],
			"Stream 0 height"
		);
		$this->assertEquals(
			"720",
			$probe["streams"][0]["width"],
			"Stream 0 width"
		);
		$this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
		$this->assertEquals(
			"Test x265 Conversion",
			$probe["format"]["tags"]["title"],
			"Metadata title"
		);
	}
}
?>
