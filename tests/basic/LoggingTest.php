<?php
/*
 * Tests when no input is given. It should product results for the file as expected.
 */
require_once "common.php";
require_once "Logger.php";

final class LoggingTests extends Test
{

    public function test_DEBUG_Log_Level() {
        $this->expectOutputRegex("/.*DEBUG::Debug level\n/");
        Logger::debug("Debug level");
    }
    
    public function test_INFO_Log_Level() {
        $this->expectOutputRegex("/.*INFO::Info level\n/");
        Logger::info("Info level");
    }

    public function test_WARN_Log_Level() {
        $this->expectOutputRegex("/.*WARN::Warning level\n/");
        Logger::warn("Warning level");
    }

    public function test_ERROR_Log_Level() {
        $this->expectOutputRegex("/.*ERROR::Error level\n/");
        Logger::error("Error level");
    }

    public function test_VERBOSE_Log_Level() {
        $this->expectOutputRegex("/.*VERBOSE::Verbose level\n/");
        Logger::verbose("Verbose level");
    }

    public function testPassingArgumentWhenNotNeeded() {
        $this->expectOutputRegex("/.*INFO::Test extra arg\n/");
        Logger::info("Test extra arg", "blah");
    }

    public function testPassingSingleArgument() {
        $this->expectOutputRegex("/.*INFO::Test single arg blah\n/");
        Logger::info("Test single arg {}", "blah");
    }

    public function testPassingMultipleArguments() {
        $this->expectOutputRegex("/.*INFO::Test one multiple two args three\n/");
        Logger::info("Test {} multiple {} args {}", "one", "two", "three");
    }

    public function testPassingArray() {
        $this->expectOutputRegex("/.*INFO::Test array arg Array\s*\(\s*\[0\] => something\s*\[1\] => somethingelse\s*\)\s*/");
        Logger::info("Test array arg {}", array("something", "somethingelse"));
    }

    public function testNumbers() {
        $this->expectOutputRegex("/.*INFO::Testing an integer=1 and float=2.35\n/");
        Logger::info("Testing an integer={} and float={}", 1, 2.35);
    }
}
?>
