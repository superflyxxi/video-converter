<?php
include_once "../php/Logger.php";

Logger::debug("Testing");
Logger::debug("Testing nothing", array(
    "blah"
));
Logger::debug("Testing something {}", array(
    "blah"
));
Logger::debug("Testing multiple things {}, {}, {}", array(
    "one",
    "two",
    "three"
));
Logger::debug("Testing an array {}", array(
    array(
        "something",
        "somethingelse"
    )
));
Logger::debug("Testing an integer={} and float={}", array(
    1,
    2.35
));

?>
