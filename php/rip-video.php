#!/usr/bin/php
<?php
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
require_once 'vendor/autoload.php';
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
$csvRequest = NULL;
if (strcasecmp(substr($envInput, -4 ), ".csv") === 0) {
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
  Logger::verbose("Files to process: {}", $arrFiles);
  $csvFile = new SplTempFileObject();
  $csvFile->fputcsv(array("filename", "dummy"));
  foreach ($arrFiles as $infile) {
    Logger::debug("Adding to CSV: {}", $infile);
    $csvFile->fputcsv(array($infile, "dummy"));
  }
  $csvFile->rewind();
  $csvRequest = new CSVRequest($csvFile);
}

$finalResult = $csvRequest->convert();
exit($finalResult);
?>
