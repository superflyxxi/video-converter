<?php
include_once "ConvertFile.php";

function error_handler(int $errno, string $errstr, string $errfile = NULL, $errline = 0, array $errcontext = NULL)
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

if (NULL == getEnv("INPUT")) {
    $arrFiles = array_diff(scandir("/data/"), array(
        '..',
        '.'
    ));
} else {
    $arrFiles[] = getEnv("INPUT");
}

Logger::verbose("Files to process: {}", $arrFiles);

$finalResult = 0;
foreach ($arrFiles as $file) {
    $conversion = new ConvertFile("/data/" . $file, getEnv("TITLE"), getEnv("YEAR"), getEnv("SEASON"), getEnv("EPISODE"), getEnv("SUBTITLE"));
    $result = $conversion->convert();
    $finalResult = max($finalResult, $result);
}
exit($finalResult);
?>
