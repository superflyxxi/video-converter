<?php
include_once "../php/Logger.php";

Logger::debug("Testing");
Logger::debug("Testing nothing", "blah");
Logger::debug("Testing something {}", "blah");
Logger::debug("Testing multiple things {}, {}, {}", "one", "two", "three");
Logger::debug("Testing an array {}", array(
    "something",
    "somethingelse"
));
Logger::debug("Testing an integer={} and float={}", 1, 2.35);

?>
