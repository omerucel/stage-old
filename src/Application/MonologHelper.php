<?php

namespace Application;

use League\Container\Container;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class MonologHelper
{
    /**
     * @var Container
     */
    protected $di;

    /**
     * @var array
     */
    protected $loggers = [];

    /**
     * @var array
     */
    protected $defaultContext = [];

    /**
     * @param Container $di
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    /**
     * @param string $name
     * @return LoggerInterface
     */
    public function getLogger($name = '')
    {
        $name = $name == '' ? $this->getConfig()->logger->default_name : trim($name);
        if (!isset($this->loggers[$name])) {
            $this->loggers[$name] = $this->createLogger($name);
        }
        return $this->loggers[$name];
    }

    /**
     * @param $name
     * @return LoggerInterface
     * @throws \Exception
     */
    public function createLogger($name)
    {
        $logger = new Logger($name);
        $stream = new StreamHandler($this->getFilePath($name), $this->getLogLevel($name));
        $stream->setFormatter(new LineFormatter("[%datetime%] [%level_name%] %message% %context%\n"));
        $logger->pushHandler($stream);
        $this->resetLogger($logger);
        return $logger;
    }

    /**
     * @param Logger $logger
     */
    public function resetLogger(Logger $logger)
    {
        if (count($logger->getProcessors()) > 0) {
            $logger->popProcessor();
        }
        $logger->pushProcessor(function ($record) {
            $record['context']['ReqID'] = $this->getConfig()->req_id;
            return $record;
        });
    }

    public function resetLoggers()
    {
        foreach (array_values($this->loggers) as $logger) {
            $this->resetLogger($logger);
        }
    }

    public function reCreateLoggers()
    {
        foreach ($this->loggers as $name => $logger) {
            unset($logger);
            $this->loggers[$name] = $this->createLogger($name);
        }
    }

    /**
     * @param $name
     * @return string
     */
    protected function getLogLevel($name)
    {
        $level = $this->getConfig()->logger->default_level;
        if (isset($this->getConfig()->logger->{$name}->level)) {
            $level = $this->getConfig()->logger->{$name}->level;
        }
        return $level;
    }

    /**
     * @param $name
     * @return string
     */
    protected function getFilePath($name)
    {
        $basePath = $this->getConfig()->logger->default_path;
        if (isset($this->getConfig()->logger->{$name}->path)) {
            $basePath = $this->getConfig()->logger->{$name}->path;
        }
        return $basePath . '/' . $name . '-' . $this->getConfig()->environment . '-' . date('Y-m-d') . '.log';
    }

    /**
     * @return \stdClass
     * @throws \Exception
     */
    public function getConfig()
    {
        return $this->di->get('config');
    }
}
