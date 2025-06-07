<?php
require_once "TestSetup.php";

final class AudioTest extends TestSetup
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

    public function testNormalizingDefaults()
    {
        $this->normalize("Test Normalize Track 1", null, 6, null);
    }

    public function testNormalizingChannelLayoutNotSide()
    {
        $this->normalize("Test Normalize with Channel Layout", "5.1", 6, null);
    }

    public function testNormalizingChannelLayoutSmaller()
    {
        $this->normalize("Test Normalize with Smaller Channel Layout", "2.1", 3, null);
    }

    public function testNormalizingWithCopy()
    {
        $this->normalize("Test Normalize with Copy", null, 6, "copy");
    }

    private function extractNormJson($file, $track)
    {
        $out = "";
        printf("Executing analysis " . $file . "\n");
        exec("ffmpeg -hide_banner -i \"" . $file . "\" -map 0:" . $track . " -filter:a loudnorm=print_format=json -f null - 2>&1", $out);
        $out = implode(array_slice($out, - 14));
        $open = strpos($out, "{");
        $close = strpos($out, "}");
        $out = substr($out, $open, $close - $open + 1);
        return json_decode($out, true);
    }

    public function normalize($title, $channelLayout, $channels, $audioFormat)
    {
        $this->getFile("dvd");
        $args = [
            "--title" => $title,
            "--year" => 2019,
            "--normalize-audio-tracks" => 1,
            "--video-tracks" => - 1,
            "--subtitle-tracks" => - 1,
            "--normalize-audio-format" => "eac3"
        ];
        if (null != $channelLayout) {
            $args["--audio-channel-layout"] = $channelLayout;
        }
        if (null != $audioFormat) {
            $args["--audio-format"] = $audioFormat;
        }
        $return = $this->ripvideo("dvd.mkv", $args);
        $this->assertEquals(0, $return, "ripvideo exit code");

        $probe = $this->probe($title . " (2019).dvd.mkv.mkv");

        $this->assertEquals("audio", $probe["streams"][0]["codec_type"], "Stream 0 codec_type");
        $this->assertEquals("ac3", $probe["streams"][0]["codec_name"], "Stream 0 codec");
        $this->assertEquals($channels, $probe["streams"][0]["channels"], "Stream 0 channels");
        $this->assertEquals("48000", $probe["streams"][0]["sample_rate"], "Stream 0 sample_rate");

        $this->assertEquals("Normalized Surround 5.1", $probe["streams"][1]["tags"]["title"], "Stream 1 title");
        $this->assertEquals("eng", $probe["streams"][1]["tags"]["language"], "Stream 1 language");
        $this->assertEquals("audio", $probe["streams"][1]["codec_type"], "Stream 1 codec_type");
        $this->assertEquals("eac3", $probe["streams"][1]["codec_name"], "Stream 1 codec");
        $this->assertEquals($channels, $probe["streams"][1]["channels"], "Stream 1 channels");
        $this->assertEquals("48000", $probe["streams"][1]["sample_rate"], "Stream 1 sample_rate");
        $this->assertArrayNotHasKey(2, $probe["streams"], "Stream 2 exists");
        $this->assertEquals($title, $probe["format"]["tags"]["title"], "Metadata title");

        // measure both and ensure they don't equal
        // original measured_I=-28.59:measured_TP=-8.10:measured_LRA=11.70:measured_thresh=-39.21
        // original results "input_i\" : \"-28.59\",","\t\"input_tp\" : \"-8.10\",","\t\"input_lra\" : \"11.70\",","\t\"input_thresh\" : \"-39.21\",
        // normalized measured "input_i" : "-28.62",   "input_tp" : "-8.11",   "input_lra" : "11.70",  "input_thresh" : "-39.22",
        // original aac measure "input_i" : "-23.85",   "input_tp" : "-1.99",   "input_lra" : "8.30",   "input_thresh" : "-34.16",
        $jsonZero = $this->extractNormJson($this->getDataDir() . DIRECTORY_SEPARATOR . $title . " (2019).dvd.mkv.mkv", 0);
        print_r($jsonZero);
        $jsonOne = $this->extractNormJson($this->getDataDir() . DIRECTORY_SEPARATOR . $title . " (2019).dvd.mkv.mkv", 1);
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
