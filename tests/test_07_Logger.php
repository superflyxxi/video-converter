<?php

include_once "../php/Logger.php";

Logger::debug("Testing");
Logger::debug("Testing nothing", array("blah"));
Logger::debug("Testing something {}", array("blah"));
Logger::debug("Testing multiple things {}, {}, {}", array("one", "two", "three"));

?>

