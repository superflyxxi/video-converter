<?php
include_once "Logger.php";

if (NULL == getEnv("TITLE")) {
    Logger::error("TITLE env variable missing");
    exit(1);
}

$conversion = new ConvertFile("/data/" . getEnvWithDefault("INPUT", "."), getEnv("TITLE"), getEnv("YEAR"), getEnv("SEASON"), getEnv("EPISODE"), getEnv("SUBTITLE"));
exit($conversion->convert());

?>
