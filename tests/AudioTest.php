<?php
require_once "Test.php";

final class AudioTest extends Test
{
    public function testChannelMappingOverrideToLowerValue()
    {
        $this->getFile("dvd");

        $return = $this->ripvideo(
            "dvd.mkv",
            [
                "--title" => "Test Channel Mapping",
                "--audio-channel-layout" => "stereo",
                "--audio-channel-layout-tracks" => 1,
                "--video-tracks" => - 1,
                "--subtitle-tracks" => - 1
            ]
        );
        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test Channel Mapping.dvd.mkv.mkv");

        $this->assertEquals("audio", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("ac3", $probe["streams"][0]["codec_name"], "Steram 0 codec");
        $this->assertEquals("stereo", $probe["streams"][0]["channel_layout"], "Stream 0 channel_layout");
        $this->assertEquals(2, $probe["streams"][0]["channels"], "Stream 0 channels");
        $this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
        $this->assertEquals("Test Channel Mapping", $probe["format"]["tags"]["title"], "Metadata title");
    }

    public function testNormalizing()
    {
        $this->getFile("dvd");

        $return = $this->ripvideo(
            "dvd.mkv",
            [
                "--title" => "Test Normalize Track 1",
                "--year" => 2019,
                "--normalize-audio-tracks" => 1,
                "--video-tracks" => - 1,
                "--subtitle-tracks" => - 1
            ]
        );
        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test Normalize Track 1 (2019).dvd.mkv.mkv");

        $this->assertEquals("audio", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("ac3", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals(6, $probe["streams"][0]["channels"], "Stream 0 channels");
        $this->assertEquals("48000", $probe["streams"][0]["sample_rate"], "Stream 0 sample_rate");
        $this->assertEquals("Normalized Surround 5.1", $probe["streams"][1]["tags"]["title"], "Stream 1 title");
        $this->assertEquals("eng", $probe["streams"][1]["tags"]["language"], "Stream 1 language");
        $this->assertEquals("audio", $probe["streams"][1]["codec_type"], "Stream 1 codec_type");
        $this->assertEquals("ac3", $probe["streams"][1]["codec_name"], "Stream 1 codec");
        $this->assertEquals(6, $probe["streams"][1]["channels"], "Stream 1 channels");
        $this->assertEquals("48000", $probe["streams"][1]["sample_rate"], "Stream 1 sample_rate");
        $this->assertArrayNotHasKey(2, $probe["streams"], "Stream 2 exists");
        $this->assertEquals("Test Normalize Track 1", $probe["format"]["tags"]["title"], "Metadata title");

        // measure both and ensure they don't equal
        // original measured_I=-28.59:measured_TP=-8.10:measured_LRA=11.70:measured_thresh=-39.21
        // original results "input_i\" : \"-28.59\",","\t\"input_tp\" : \"-8.10\",","\t\"input_lra\" : \"11.70\",","\t\"input_thresh\" : \"-39.21\",
        // normalized measured "input_i" : "-28.62",   "input_tp" : "-8.11",   "input_lra" : "11.70",  "input_thresh" : "-39.22",
        // original aac measure "input_i" : "-23.85",   "input_tp" : "-1.99",   "input_lra" : "8.30",   "input_thresh" : "-34.16",
        $output = "";
        printf("Executing analysis 0\n");
        exec("ffmpeg -hide_banner -i \"" . $this->getDataDir() . DIRECTORY_SEPARATOR . "Test Normalize Track 1 (2019).dvd.mkv.mkv\" -map 0:0 -filter:a loudnorm=print_format=json -f null - 2>&1", $output);
        $output = implode(array_slice($output, - 12));
        $jsonZero = json_decode($output, true);
        print_r($jsonZero);
        printf("Executing analysis 1\n");
        exec("ffmpeg -hide_banner -i \"" . $this->getDataDir() . DIRECTORY_SEPARATOR . "Test Normalize Track 1 (2019).dvd.mkv.mkv\" -map 0:1 -filter:a loudnorm=print_format=json -f null - 2>&1", $output);
        $output = implode(array_slice($output, - 12));
        $jsonOne = json_decode($output, true);
        print_r($jsonOne);
        $this->assertNotEquals($jsonZero["input_i"], $jsonOne["input_i"], "input_i");
        $this->assertNotEquals($jsonZero["input_tp"], $jsonOne["input_tp"], "input_tp");
        $this->assertNotEquals($jsonZero["input_lra"], $jsonOne["input_lra"], "input_lra");
        $this->assertNotEquals($jsonZero["input_thresh"], $jsonOne["input_thresh"], "input_thresh");
    }
    
    public function testOverrideDefaults()
    {
        $this->getFile("dvd");

        $return = $this->ripvideo(
            "dvd.mkv",
            [
                "--title" => "Test Override Default Audio Format",
                "--audio-format" => "aac",
                "--audio-quality" => 2,
                "--video-tracks" => - 1,
                "--subtitle-tracks" => - 1
            ]
        );
        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe("Test Override Default Audio Format.dvd.mkv.mkv");

        $this->assertEquals("audio", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("aac", $probe["streams"][0]["codec_name"], "Steram 0 codec");
        $this->assertEquals(6, $probe["streams"][0]["channels"], "Stream 0 channels");
        $this->assertArrayNotHasKey(1, $probe["streams"], "Stream 1 exists");
    }
}
