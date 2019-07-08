<?php


namespace MService\Payment\Shared\SharedModels;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log
{
    protected $logger;

    /*
     * Logger constructors
     */
    public function __construct($path = __DIR__ . '/test.log', $channelName = 'default', $level = Logger::DEBUG)
    {
        $this->logger = new Logger($channelName);
        $consoleHandler = new StreamHandler('php://stdout', $level);
        $fileHandler = new StreamHandler($path, $level);

        $consoleHandler->setFormatter(new ColoredLineFormatter());
        $fileHandler->setFormatter(new ColoredLineFormatter());

        $this->logger->pushHandler($consoleHandler);
        $this->logger->pushHandler($fileHandler);
    }

    public function getLogger()
    {
        return $this->logger;
    }
}