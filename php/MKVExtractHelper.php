<?php
include_once "Logger.php";

class MKVExtractHelper
{

    public static function extractTracks($oInputFile, $arrTracks, $exit = FALSE)
    {
        Logger::info("Extracting {}", $oInputFile->getFileName());
        $command = 'mkvextract tracks "' . $oInputFile->getFileName() . '" ';
        foreach ($arrTracks as $track => $outFileName) {
            $command .= ' "' . $track . ':' . $outFileName . '"';
        }
        Logger::debug("extracting with mkvextract with command: {}", $command);
        passthru($command, $return);
        if (0 < $return) {
            Logger::error("Problem executing. Got {}", $return);
            if ($exit) {
                exit($return);
            }
        }
        return $return;
    }
}

?>
