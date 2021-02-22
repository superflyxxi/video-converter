<?php

include_once "Logger.php";

class ExecutionException extends Exception {

    private $args = NULL;

    public function __construct(string $program, int $err, $args = NULL) {
        parent::__construct("Program (" . $program . ") failed to execute. Exited with ". $err, $err);
        $this->args = $args;
	Logger::error($this->getMessage());
    }

}
?>

