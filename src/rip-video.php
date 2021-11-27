#!/usr/bin/php
<?php
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
require_once __DIR__ . "/../vendor/autoload.php";
require_once "convert/ConvertFile.php";
require_once "request/CSVRequest.php";
require_once "LogWrapper.php";
require_once "Options.php";

$log = new LogWrapper("rip-video");

function error_handler(int $errno, string $errstr, $errfile = null, $errline = 0, $errcontext = null) {
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
set_error_handler("error_handler");

if (null == Options::get("title")) {
	$log->error("title missing");
	exit(1);
}

$envInput = Options::getInputFile();
$csvRequest = null;
if (strcasecmp(substr($envInput, -4), ".csv") === 0) {
	$csvRequest = new CSVRequest(new SplFileObject($envInput, "r"));
} else {
	if (null == $envInput) {
		$arrFiles = array_diff(scandir("."), ["..", "."]);
	} else {
		$arrFiles[] = $envInput;
	}
	$log->debug("Files to process", ["arrFiles" => $arrFiles]);
	$csvFile = new SplTempFileObject();
	$csvFile->fputcsv(["filename", "dummy"]);
	foreach ($arrFiles as $infile) {
		$log->debug("Adding to CSV", ["filename" => $infile]);
		$csvFile->fputcsv([$infile, "dummy"]);
	}
	$csvFile->rewind();
	$csvRequest = new CSVRequest($csvFile);
}

$finalResult = $csvRequest->convert();
exit($finalResult);


?>
