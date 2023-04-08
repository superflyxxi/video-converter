<?php
namespace SuperFlyXXI\VideoConverter;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LogWrapper extends Logger
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->pushHandler(new StreamHandler("php://stdout", Options::get("log-level", Logger::INFO)));
    }

    public function isDebugEnabled()
    {
        return $this->isHandling(Logger::DEBUG);
    }
}
