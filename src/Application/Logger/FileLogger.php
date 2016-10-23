<?php

namespace Application\Logger;

use Psr\Log\AbstractLogger;

class FileLogger extends AbstractLogger
{
    protected $name = 'default';
    protected $reqId;
    protected $environment;
    protected $logPath;
    protected $fileResource;
    protected $fileResourceDate;

    /**
     * @param $name
     * @param $environment
     * @param $logPath
     */
    public function __construct($name, $environment, $logPath)
    {
        $this->name = $name;
        $this->environment = $environment;
        $this->logPath = $logPath;
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
        $context['ReqId'] = $this->reqId;
        if ($message instanceof \Exception) {
            $message = '[' . $message->getCode() . '] ' . $message->getMessage() . ' ' . $message->getTraceAsString();
            $message = str_replace("\n", '', $message);
        }
        if (empty($context) == false) {
            $message.= ' ' . json_encode($context);
        }
        $logLine = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($level) . '] ' . $message . PHP_EOL;
        fwrite($this->getFileResource(), $logLine);
    }

    /**
     * @return mixed
     */
    protected function getFileResource()
    {
        if ($this->fileResourceDate != date('Y-m-d')) {
            if ($this->fileResource != null) {
                fclose($this->fileResource);
            }
            $filePath = realpath($this->logPath) . '/' . $this->name . '-'
                . $this->environment . '-' . date('Y-m-d') . '.log';
            $this->fileResource = fopen($filePath, 'a');
            $this->fileResourceDate = date('Y-m-d');
        }
        return $this->fileResource;
    }

    /**
     * @param mixed $reqId
     * @return $this
     */
    public function setReqId($reqId)
    {
        $this->reqId = $reqId;
        return $this;
    }
}
