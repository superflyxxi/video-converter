<?php
require_once "LogWrapper.php";

class ExecutionException extends Exception {

	public static $log;

    private $args = NULL;

    public function __construct(string $program, int $err, $args = NULL) {
        parent::__construct("Program (" . $program . ") failed to execute. Exited with ". $err, $err);
        $this->args = $args;
	self::$log->error($this->getMessage());
    }

}

ExecutionException::$log = new LogWrapper('ExecutionException');
?>
