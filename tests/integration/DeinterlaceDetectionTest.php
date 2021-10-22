<?php

require_once "common.php";

final class DeinterlaceDetectionTest extends Test {
	public function testProbeAutoDeinterlace() {
		$this->getFile("dvd");

		$return = $this->ripvideo(
			"dvd.mkv",
			[
				"--deinterlace" => "00",
				"--audio-tracks" => -1,
				"--subtitle-tracks" => -1,
				"--title" => "Test Probe Auto Deinterlace",
				"--year" => 2019,
			],
			"1m"
		);

		// not validating return as it can be killed; test("ffmpeg code", 0, $return, $testOutput);
		//        $this->assertEquals(0, $return, "ripvideo exit code");

		$probe = $this->probe("Test Probe Auto Deinterlace (2019).dvd.mkv.mkv");

		$this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
		$this->assertEquals("hevc", $probe["streams"][0]["codec_name"], "Stream 0 codec");
		// doesn't exist... test("Stream 0 field_order", "progressive", $probe["streams"][0]["field_order"], $testOutput);
		$this->assertEquals("24000/1001", $probe["streams"][0]["r_frame_rate"], "Stream 0 r_frame_rate");
		$this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
		$this->assertEquals("Test Probe Auto Deinterlace", $probe["format"]["tags"]["title"], "Metadata title");
		$this->assertEquals("2019", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
		$this->assertArrayNotHasKey("SEASON", $probe["format"]["tags"], "Metadata SEASON");
		$this->assertArrayNotHasKey("EPISODE", $probe["format"]["tags"], "Metadata EPISODE");
		$this->assertArrayNotHasKey("SUBTITLE", $probe["format"]["tags"], "Metadata SUBTITLE");
	}

	public function testIdetAutoDeinterlace() {
		$this->getFile("dvd");

		$return = $this->ripvideo(
			"dvd.mkv",
			[
				"--deinterlace" => "00",
				"--deinterlace-check" => "idet",
				"--audio-tracks" => -1,
				"--subtitle-tracks" => -1,
				"--title" => "Test Idet Auto Deinterlace",
				"--year" => 2021,
			],
			"1m"
		);

		// not validating return as it can be killed; test("ffmpeg code", 0, $return, $testOutput);
		//        $this->assertEquals(0, $return, "ripvideo exit code");

		$probe = $this->probe("Test Idet Auto Deinterlace (2021).dvd.mkv.mkv");

		$this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
		$this->assertEquals("hevc", $probe["streams"][0]["codec_name"], "Stream 0 codec");
		$this->assertEquals("24000/1001", $probe["streams"][0]["r_frame_rate"], "Stream 0 r_frame_rate");
		$this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
		$this->assertEquals("Test Idet Auto Deinterlace", $probe["format"]["tags"]["title"], "Metadata title");
		$this->assertEquals("2021", $probe["format"]["tags"]["YEAR"], "Metadata YEAR");
	}
}
