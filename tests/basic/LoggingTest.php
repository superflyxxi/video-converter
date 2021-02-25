<?php
/*
 * Tests when no input is given. It should product results for the file as expected.
 */
require_once "common.php";
require_once "Logger.php";

final class LoggingTests extends Test
{
    public function testLoggingProducesNoErrors() {
        Logger::debug("Testing");
        Logger::debug("Testing nothing", "blah");
        Logger::debug("Testing something {}", "blah");
        Logger::debug("Testing multiple things {}, {}, {}", "one", "two", "three");
        Logger::debug("Testing an array {}", array("something","somethingelse"));
        Logger::debug("Testing an integer={} and float={}", 1, 2.35);
        $this->assertTrue(true, "Test ran without error");
    }
}
?>
