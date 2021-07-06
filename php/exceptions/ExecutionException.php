<?php

require_once "LogWrapper.php";

ExecutionException::$log = new LogWrapper('ExecutionException');

class ExecutionException extends Exception {

	private static $log;

    private $args = NULL;

    public function __construct(string $program, int $err, $args = NULL) {
        parent::__construct("Program (" . $program . ") failed to execute. Exited with ". $err, $err);
        $this->args = $args;
	self::$log->error($this->getMessage());
    }

}
?>

