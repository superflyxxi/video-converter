<?php
namespace SuperFlyXXI\VideoConverter\Helpers;

use SuperFlyXXI\VideoConverter\LogWrapper;
use SuperFlyXXI\VideoConverter\Exceptions\ExecutionException;
use SuperFlyXXI\VideoConverter\Input\InputFile;
use SuperFlyXXI\VideoConverter\Helpers\EnvHelper;
use SuperFlyXXI\VideoConverter\Generators\ffmpeg\FFmpegArgGenerator;
use SuperFlyXXI\VideoConverter\Generators\ffmpeg\FFmpegAudioArgGenerator;
use SuperFlyXXI\VideoConverter\Generators\ffmpeg\FFmpegVideoArgGenerator;
use SuperFlyXXI\VideoConverter\Generators\ffmpeg\FFmpegSubtitleArgGenerator;

class FFmpegHelper
{
    public static $log;

    private static $INTERLACED_REPLACEMENT_REGEX = "/[A-Za-z]+:[ ]+([0-9]+)/";

    private static $probeCache = [];

    public static function probe(InputFile $inputFile): array
    {
        if (! array_key_exists($inputFile->getFileName(), self::$probeCache)) {
            $command = 'ffprobe -v quiet -print_format json -show_format -show_streams "' . $inputFile->getPrefix() .
                $inputFile->getFileName() . '"';
            self::$log->info("Executing ffprobe", [
                "command" => $command
            ]);
            exec($command, $out, $ret);
            if ($ret > 0) {
                self::$log->error(print_r($out, true));
                throw new ExecutionException("ffprobe", $ret);
            }
            $json = json_decode(implode($out), true);
            self::$log->debug("Adding to probe cache", [
                "filename" => $inputFile->getFileName(),
                "result" => $json
            ]);
            self::$probeCache[$inputFile->getFileName()] = $json;
        } else {
            self::$log->debug("Found in probe cache", [
                "filename" => $inputFile->getFileName()
            ]);
            $json = self::$probeCache[$inputFile->getFileName()];
        }
        return $json;
    }

    public static function isInterlacedBasedOnProbe(InputFile $inputFile): bool
    {
        $json = self::probe($inputFile);
        $stream = $json["streams"][0];
        return array_key_exists("field_order", $stream) && $stream["field_order"] != "progressive";
    }

    public static function isInterlacedBasedOnIdet(InputFile $inputFile): bool
    {
        self::$log->info("Checking for interlacing", [
            "filename" => $inputFile->getFileName()
        ]);
        $args = '-i "' . $inputFile->getFileName()
                . '" -ss 00:05:00 -to 00:10:00 -vf idet -f rawvideo -y /dev/null 2>&1';
        $command = "ffmpeg " . $args;
        self::$log->info("Checking for interlace", [
            "command" => $command
        ]);
        exec($command, $out, $ret);
        if ($ret > 0) {
            throw new ExecutionException("ffmpeg", $ret, $args);
        }
        self::$log->debug("Interlacing output", [
            "output" => $out
        ]);
        $out = implode($out);
        /*
         * [Parsed_idet_0 @ 0x559b53b4b700] Repeated Fields: Neither: 14385 Top: 1 Bottom: 2
         * [Parsed_idet_0 @ 0x559b53b4b700] Single frame detection: TFF: 10 BFF: 13 Progressive: 8535 Undetermined: 5830
         * [Parsed_idet_0 @ 0x559b53b4b700] Multi frame detection: TFF: 0 BFF: 0 Progressive: 14365 Undetermined: 23
         */
        preg_match("/Progressive:\s+(\d+)/", $out, $matches);
        $progressive = preg_replace(self::$INTERLACED_REPLACEMENT_REGEX, "$1", $matches[0]);
        preg_match("/TFF:\s+(\d+)/", $out, $matches);
        $tff = preg_replace(self::$INTERLACED_REPLACEMENT_REGEX, "$1", $matches[0]);
        preg_match("/BFF:\s+(\d+)/", $out, $matches);
        $bff = preg_replace(self::$INTERLACED_REPLACEMENT_REGEX, "$1", $matches[0]);
        $total = $progressive + $tff + $bff;
        self::$log->debug(
            "Interlacing probe results",
            [
                "progressive" => $progressive,
                "tff" => $tff,
                "bff" => $bff,
                "total" => $total
            ]
        );
        // if percentage of frames are > 1% interlaced, then de-interlace
        return $tff / $total > 0.01 || $bff / $total > 0.01;
    }

    public static function execute($listRequests, $outputFile)
    {
        $command = self::generate($listRequests, $outputFile);
        self::$log->notice("Executing ffmpeg", [
            "command" => $command
        ]);
        passthru($command . " 2>&1", $ret);
        self::$log->debug("Command return result", [
            "result" => $ret
        ]);
        if ($ret > 0) {
            throw new ExecutionException("ffmpeg", $ret, $command);
        }
        return $ret;
    }

    public static function generate($listRequests, $outputFile)
    {
        $finalCommand = "ffmpeg -stats_period 30 ";
        if (EnvHelper::getEnvWithDefault("OVERWRITE_FILE", "true") == "true") {
            $finalCommand .= "-y ";
        }
        $finalCommand .= self::generateHardwareAccelArgs($listRequests);

        // generate input args
        foreach ($listRequests as $tmpRequest) {
            $finalCommand .= ' -i "' . $tmpRequest->oInputFile->getPrefix() . $tmpRequest->oInputFile->getFileName() .
                '" ';
        }

        self::$log->debug("Generating video args");
        $finalCommand .= " " . self::generateArgs($listRequests, new FFmpegVideoArgGenerator());

        self::$log->debug("Generating audio args");
        $finalCommand .= " " . self::generateArgs($listRequests, new FFmpegAudioArgGenerator());

        self::$log->debug("Generating subtitle args");
        $finalCommand .= " " . self::generateArgs($listRequests, new FFmpegSubtitleArgGenerator());

        $finalCommand .= self::generateGlobalMetadataArgs($outputFile);
        if ($outputFile->format != null) {
            $finalCommand .= " -f " . $outputFile->format;
        }
        $finalCommand .= ' "' . $outputFile->getFileName() . '"';

        return $finalCommand;
    }

    private static function generateHardwareAccelArgs($listRequests)
    {
        $decode = "";
        $encode = "";
        foreach ($listRequests as $tmpRequest) {
            if ($decode == "" && $tmpRequest->isHwAccelDecode()) {
                $decode = "-hwaccel vaapi -hwaccel_device /dev/dri/renderD128";
            }
            if ($encode == "" && $tmpRequest->isHwAccelEncode()) {
                $encode = "-vaapi_device /dev/dri/renderD128";
            }
        }
        return $decode . " " . $encode;
    }

    private static function generateGlobalMetadataArgs($outputFile)
    {
        return " " . (null != $outputFile->title ? '-metadata "title=' . $outputFile->title . '"' : " ") . " " .
            (null != $outputFile->showTitle ? '-metadata "showTitle=' . $outputFile->showTitle . '"' : " ") . " " .
            (null != $outputFile->year ? '-metadata "year=' . $outputFile->year . '"' : " ") . " " .
            (null != $outputFile->season ? '-metadata "season=' . $outputFile->season . '"' : " ") . " " .
            (null != $outputFile->episode ? '-metadata "episode=' . $outputFile->episode . '"' : " ") . " " .
            EnvHelper::getEnvWithDefault("OTHER_METADATA", " ");
    }

    private static function generateArgs($listRequests, FFmpegArgGenerator $generator)
    {
        $fileno = 0;
        $outTrack = 0;
        $args = " ";
        foreach ($listRequests as $tmpRequest) {
            $streamList = $generator->getStreams($tmpRequest->oInputFile);
            self::$log->debug(
                "Generating args",
                [
                    "fileno" => $fileno,
                    "filename" => $tmpRequest->oInputFile->getFileName()
                ]
            );
            foreach ($streamList as $index => $stream) {
                self::$log->debug(
                    "Generating stream specific args",
                    [
                        "fileno" => $fileno,
                        "index" => $index,
                        "stream" => $stream
                    ]
                );
                $thisarg = " -map " . $fileno . ":" . $index;
                $thisarg .= " " . $generator->getAdditionalArgs($outTrack ++, $tmpRequest, $index, $stream);
                $args.=$thisarg;
                self::$log->debug("Final argument generated", ["fileno"=>$fileno, "index"=>$index, "arg"=>$thisarg]);
            }
            $fileno ++;
        }
        return $args;
    }
}

FFmpegHelper::$log = new LogWrapper("FFmpegHelper");
