<?php
require_once "common.php";

final class SimpleScaleTest extends Test {
	public function scaling(): array {
		return [["1.5", "720", "1080"], ["0.5", "240", "360"]];
	}

	/**
	 * @test
	 * @dataProvider scaling
	 */
	public function testScaling($factor, $height, $width) {
		$this->getFile("dvd");

		$return = $this->ripvideo(
			"dvd.mkv",
			[
				"--title" => "Test " . $factor . "x Scale",
				"--video-upscale" => $factor,
				"--audio-tracks" => -1,
				"--subtitle-tracks" => -1,
				"--deinterlace" => "off",
			],
			"10m"
		);
		$this->assertEquals(0, $return, "ripvideo exit code");

		$probe = $this->probe("Test " . $factor . "x Scale.dvd.mkv.mkv");

		$this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
		$this->assertEquals("hevc", $probe["streams"][0]["codec_name"], "Stream 0 codec");
		$this->assertEquals($height, $probe["streams"][0]["height"], "Stream 0 height");
		$this->assertEquals($width, $probe["streams"][0]["width"], "Stream 0 width");
		$this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
		$this->assertEquals(
			"Test " . $factor . "x Simple Upscale",
			$probe["format"]["tags"]["title"],
			"Metadata title"
		);
	}
}
?>
