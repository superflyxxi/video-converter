<?php
require_once "LogWrapper.php";
require_once "exceptions/ExecutionException.php";

class MKVExtractHelper
{
	public static $log;

    public static function extractTracks($oInputFile, $arrTracks)
    {
        self::$log->info("Extracting", array('filename'=>$oInputFile->getFileName()));
        $command = 'mkvextract tracks "' . $oInputFile->getFileName() . '" ';
        foreach ($arrTracks as $track => $outFileName) {
            $command .= ' "' . $track . ':' . $outFileName . '"';
        }
        self::$log->debug("extracting with mkvextract with command", array('command'=>$command));
        passthru($command, $return);
        if (0 < $return) {
	    throw new ExecutionException("mkvextract", $return, $command);
        }
        return $return;
    }
}

MKVExtractHelper::$log = new LogWrapper('MKVExtractHelper');
?>
