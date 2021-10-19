<?php
require_once "common.php";

final class DeinterlaceModeTest extends Test {
	public function testDeinterlaceMode00() {
		$this->assertTrue(true, "Already covered by auto-detection tests");
	}

	public function testDeinterlaceMode01() {
		$this->getFile("dvd");

		$return = $this->ripvideo(
			"dvd.mkv",
			[
				"--deinterlace" => "01",
				"--audio-tracks" => -1,
				"--subtitle-tracks" => -1,
				"--title" => "Test Deinterlace Mode 01",
				"--year" => 2021,
			],
			"1m"
		);

		$probe = $this->probe("Test Deinterlace Mode 01 (2021).dvd.mkv.mkv");

		$this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
		$this->assertEquals("hevc", $probe["streams"][0]["codec_name"], "Stream 0 codec");
		$this->assertEquals("19001/317", $probe["streams"][0]["r_frame_rate"], "Stream 0 r_frame_rate");
		$this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
		$this->assertEquals("Test Deinterlace Mode 01", $probe["format"]["tags"]["title"], "Metadata title");
		$this->assertEquals("2021", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
	}

	public function testDeinterlaceMode02() {
		$this->getFile("dvd");

		$return = $this->ripvideo(
			"dvd.mkv",
			[
				"--deinterlace" => "02",
				"--audio-tracks" => -1,
				"--subtitle-tracks" => -1,
				"--title" => "Test Deinterlace Mode 02",
				"--year" => 2021,
			],
			"1m"
		);

		$probe = $this->probe("Test Deinterlace Mode 02 (2021).dvd.mkv.mkv");

		$this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
		$this->assertEquals("hevc", $probe["streams"][0]["codec_name"], "Stream 0 codec");
		$this->assertEquals("30000/1001", $probe["streams"][0]["r_frame_rate"], "Stream 0 r_frame_rate");
		$this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
		$this->assertEquals("Test Deinterlace Mode 02", $probe["format"]["tags"]["title"], "Metadata title");
		$this->assertEquals("2021", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
	}
}
?>
