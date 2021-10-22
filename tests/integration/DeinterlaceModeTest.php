<?php
require_once "common.php";

final class DeinterlaceModeTest extends Test {
	public function dataProvider(): array {
		return ["double framerate" => ["01", "19001/317"], "same framerate" => ["02", "30000/1001"]];
	}

	/** @test */
	public function testDeinterlaceMode01() {
		$this->assertTrue(true, "Already covered by auto-detection tests");
	}

	/**
	 * @test
	 * @dataProvider dataProvider
	 */
	public function testDeinterlaceModes($mode, $expectedFramerate) {
		$this->getFile("dvd");

		$return = $this->ripvideo(
			"dvd.mkv",
			[
				"--deinterlace" => $mode,
				"--audio-tracks" => -1,
				"--subtitle-tracks" => -1,
				"--title" => "Test Deinterlace Mode " . $mode,
				"--year" => 2021,
			],
			"1m"
		);

		$probe = $this->probe("Test Deinterlace Mode " . $mode . " (2021).dvd.mkv.mkv");

		$this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
		$this->assertEquals("hevc", $probe["streams"][0]["codec_name"], "Stream 0 codec");
		$this->assertEquals($expectedFramerate, $probe["streams"][0]["r_frame_rate"], "Stream 0 r_frame_rate");
		$this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
		$this->assertEquals("Test Deinterlace Mode " . $mode, $probe["format"]["tags"]["title"], "Metadata title");
		$this->assertEquals("2021", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
	}
}
?>
