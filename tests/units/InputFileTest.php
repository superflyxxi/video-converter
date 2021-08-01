<?php
/*
 * Tests when no input is given. It should product results for the file as expected.
 */
require_once "common.php";
require_once "InputFile.php";

final class InputFileTests extends Test
{
	public function testInputFileDVDmkv() {
		$this->getFile("dvd");
		$file = new InputFile($this->getDataDir() . DIRECTORY_SEPARATOR . "dvd.mkv");
		$videoStreams = $file->getVideoStreams();
		$this->assertEquals(1, count($videoStreams), "Video Streams");
		$this->markTestIncomplete("Still need to add many assertions");
	}
}
