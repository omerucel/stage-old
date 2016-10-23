<?php

namespace Application\Logger;

use Psr\Log\LoggerInterface;

class LoggerHelper
{
    /**
     * @var array
     */
    protected $loggers = [];

    /**
     * @var string
     */
    protected $reqId;

    /**
     * @var string
     */
    protected $logPath;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var string
     */
    protected $defaultName;

    /**
     * @param string $logPath
     * @param string $environment
     * @param string $defaultName
     */
    public function __construct($logPath = '/tmp', $environment = 'development', $defaultName = 'default')
    {
        $this->logPath = $logPath;
        $this->environment = $environment;
        $this->defaultName = $defaultName;
    }

    /**
     * @param $name
     * @return LoggerInterface
     */
    public function getLogger($name = null)
    {
        $name = $name == null ? $this->defaultName : $name;
        if (isset($this->loggers[$name]) == false) {
            $logger = new FileLogger($name, $this->environment, $this->logPath);
            $logger->setReqId($this->reqId);
            $this->loggers[$name] = $logger;
        }
        return $this->loggers[$name];
    }

    /**
     * @param $reqId
     * @return $this
     */
    public function setReqId($reqId)
    {
        $this->reqId = $reqId;
        foreach ($this->loggers as $logger) {
            $logger->setReqId($reqId);
        }
        return $this;
    }

    /**
     * @param string $defaultName
     */
    public function setDefaultName($defaultName)
    {
        $this->defaultName = $defaultName;
    }
}
