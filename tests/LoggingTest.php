<?php
include_once "common.php";
include_once "Logger.php";

use PHPUnit\Framework\TestCase;

final class LoggingTest extends TestCase
{

    public function testNoErrors() {
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
