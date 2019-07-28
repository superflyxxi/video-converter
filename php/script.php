<?php
include_once "ConvertFile.php";

if (NULL == getEnv("TITLE")) {
    Logger::error("TITLE env variable missing");
    exit(1);
}

if (NULL == getEnv("INPUT")) {
    $arrFiles = scandir("/data/");
} else {
    $arrFiles[] = getEnv("INPUT");
}

Logger::verbose("Files to process: {}", array(
    $arrFiles
));
foreach ($arrFiles as $file) {
    $conversion = new ConvertFile("/data/" . getEnvWithDefault("INPUT", "."), getEnv("TITLE"), getEnv("YEAR"), getEnv("SEASON"), getEnv("EPISODE"), getEnv("SUBTITLE"));
    $result = $conversion->convert();
    if ($result != 0) {
        exit($result);
    }
}
exit(0);
?>
