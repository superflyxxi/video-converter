<?php
namespace SuperFlyXXI\VideoConverter\Normalizers;

use SuperFlyXXI\VideoConverter\LogWrapper;
use SuperFlyXXI\VideoConverter\Exceptions\ExecutionException;

class VolumeAnalyzer
{
    public static $log;

    /**
     *
     * @param inFileName The filename to analyze.
     * @param index The index of the file to analyze.
     */
    public static function analyzeAudio(string $inFileName, int $index): array
    {
        self::$log->info("Analyzing audio track", [
            "filename" => $inFileName,
            "index" => $index
        ]);
        $command = 'ffmpeg -hide_banner -i "' . $inFileName . '" -map 0:' . $index .
            ' -filter:a loudnorm=print_format=json -f null - 2>&1';
        self::$log->debug("Analyzing audio for normalization", [
            "filename" => $inFileName,
            "index" => $index
        ]);
        self::$log->notice("Executing command", [
            "command" => $command
        ]);
        exec($command, $out, $return);
        if ($return != 0) {
            throw new ExecutionException("ffmpeg", $return, $command);
        }
        self::$log->debug("Command output", [
            "output" => $out
        ]);
        $out = implode(array_slice($out, - 12));
        $json = json_decode($out, true);
        return $json;
    }
}

VolumeAnalyzer::$log = new LogWrapper("VolumeAnalyzer");
