<?php
include_once "Logger.php";

Logger::info("Starting conversion");

if (NULL == getEnv("TITLE")) {
    Logger::error("TITLE env variable missing");
    exit(1);
}

$conversion = new ConvertFile(getEnvWithDefault("INPUT", "."), getEnv("TITLE"), getEnv("YEAR"), getEnv("SEASON"), getEnv("EPISODE"), getEnv("SUBTITLE"));
exit($conversion->convert());

?>
