<?php

namespace Application;

use Psr\Log\LoggerInterface;

class Nginx
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $nginxBin;

    /**
     * @param $nginxBin
     * @param LoggerInterface|null $logger
     */
    public function __construct($nginxBin, LoggerInterface $logger = null)
    {
        $this->nginxBin = $nginxBin;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function reload()
    {
        return $this->nginxExec(['-s', 'reload']);
    }

    /**
     * @param array $args
     * @return array
     */
    protected function nginxExec(array $args = array())
    {
        array_unshift($args, $this->nginxBin);
        $cmd = 'sudo ' . implode(' ', $args) . ' 2>&1';
        exec($cmd, $output, $exitCode);
        if ($exitCode !== 0 && $this->logger != null) {
            $this->logger->error($cmd, ['Output' => json_encode($output), 'ExitCode' => $exitCode]);
        }
        return [
            'output' => $output,
            'exitCode' => $exitCode
        ];
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}
