#!/usr/bin/php
<?php
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
require_once "convert/ConvertFile.php";
require_once "request/CSVRequest.php";

function error_handler(int $errno, string $errstr, $errfile = NULL, $errline = 0, $errcontext = NULL)
{
    print_r("Error encountered! ");
    print_r($errstr);
    print_r(" at file ");
    print_r($errfile);
    print_r(":");
    print_r($errline);
    print_r("\n");
    print_r($errcontext);
    ob_flush();
    flush();
    exit($errno);
}
set_error_handler('error_handler');

if (NULL == getEnv("TITLE")) {
    Logger::error("TITLE env variable missing");
    exit(1);
}

$envInput = getEnv("INPUT");
Logger::info("INPUT=$envInput");
$csvRequest = NULL;
if (substr($envInput, -4 ) === ".csv") {
  $csvRequest = new CSVRequest(new SplFileObject("/data/".$envInput, "r"));
} else {
  if (NULL == $envInput) {
    $arrFiles = array_diff(scandir("/data/"), array(
        '..',
        '.'
    ));
  } else {
    $arrFiles[] = $envInput;
  }
  Logger::info("arrFiles={}", $arrFiles);
  //$csvFilename = getEnvWithDefault("TMP_DIR", "/tmp")."/tmp.csv"; 
  //$csvFile = new SplFileObject($csvFilename, "w");
  $csvFile = new SplTempFileObject();
  $csvFile->fputcsv(array("filename"));
  foreach ($arrFiles as $infile) {
    $csvFile->fputcsv(array($infile));
  }
  $csvFile->rewind();
  $csvRequest = new CSVRequest($csvFile);
}

Logger::verbose("Files to process: {}", $arrFiles);

$finalResult = 0;
foreach ($arrFiles as $file) {
    try {
        $conversion = new ConvertFile("/data/" . $file, getEnv("TITLE"), getEnv("YEAR"), getEnv("SEASON"), getEnv("EPISODE"), getEnv("SUBTITLE"));
        $result = $conversion->convert(Request::newInstanceFromEnv("/data/".$file));
    } catch (Exception $ex) {
        Logger::error("Got exception for file {}: {}", $file, $ex->getMessage());
        $result = 255;
    } finally {
        $finalResult = isset($result) ? max($finalResult, $result) : $finalResult;
    }
}
exit($finalResult);
?>
