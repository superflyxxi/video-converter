<?php
namespace SuperFlyXXI\VideoConverter;

use SuperFlyXXI\VideoConverter\Requests\CSVRequest;
use SplFileObject;
use SplTempFileObject;

class RipVideo
{
    public static $log;

    public function rip()
    {
        $envInput = Options::getInputFile() ?? "";
        $csvRequest = null;
        if (strcasecmp(substr($envInput, - 4), ".csv") === 0) {
            $csvRequest = new CSVRequest(new SplFileObject($envInput, "r"));
        } else {
            if (null == $envInput) {
                $arrFiles = scandir(".");
            } else {
                $arrFiles[] = $envInput;
            }
            self::$log->debug("Files to process", [
                "arrFiles" => $arrFiles
            ]);
            $csvFile = new SplTempFileObject();
            $csvFile->fputcsv([
                "filename",
                "dummy"
            ]);
            foreach ($arrFiles as $infile) {
                if (! is_dir($infile)) {
                    self::$log->debug("Adding to CSV", [
                        "filename" => $infile
                    ]);
                    $csvFile->fputcsv([
                        $infile,
                        "dummy"
                    ]);
                }
            }
            $csvFile->rewind();
            $csvRequest = new CSVRequest($csvFile);
        }

        return $csvRequest->convert();
    }
}

RipVideo::$log = new LogWrapper("RipVideo");
