<?php
require_once "common.php";

final class UpscaleVaapiTests extends Test
{

	public function testUpscalingUsingVaapi() {
		$this->getFile("dvd");

		$return = $this->ripvideo(array("INPUT"=>"dvd.mkv", "TITLE"=>"Test 2.25x Vaapi Upscale", "VIDEO_UPSCALE"=>"2.25", "AUDIO_TRACKS"=>-1, "SUBTITLE_TRACKS"=>-1));
//		$this->assertEquals(0, $return, "ripvideo exit code");

		$probe = $this->probe("Test 2.25x Vaapi Upscale.dvd.mkv.mkv");

		$this->assertEquals("video", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
		$this->assertEquals("hevc", $probe["streams"][0]["codec_name"], "Stream 0 codec");
		$this->assertEquals("1080", $probe["streams"][0]["height"], "Stream 0 height");
		$this->assertEquals("1440", $probe["streams"][0]["width"], "Stream 0 width");
		$this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
		$this->assertEquals("Test 2.25x Vaapi Upscale", $probe["format"]["tags"]["title"], "Metadata title");
	}
}
?>
