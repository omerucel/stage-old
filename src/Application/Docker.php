<?php

namespace Application;

use Psr\Log\LoggerInterface;

class Docker
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $dockerBin;

    /**
     * @var string
     */
    protected $dockerComposeBin;

    /**
     * @param $dockerBin
     * @param $dockerComposeBin
     * @param LoggerInterface|null $logger
     */
    public function __construct($dockerBin, $dockerComposeBin, LoggerInterface $logger = null)
    {
        $this->dockerBin = $dockerBin;
        $this->dockerComposeBin = $dockerComposeBin;
        $this->logger = $logger;
    }

    /**
     * @param $directory
     * @return array
     */
    public function getContainersInfo($directory)
    {
        $containers = [];
        $response = $this->composeExec(['-f', $directory . '/docker-compose.yml', 'ps', '-q']);
        if ($response['exitCode'] == 0) {
            foreach ($response['output'] as $id) {
                $inspectResponse = $this->inspect($id);
                if ($inspectResponse['exitCode'] == 0) {
                    $containers[] = json_decode($inspectResponse['output'][0], true);
                }
            }
        }
        return $containers;
    }

    /**
     * @param $directory
     * @param null $serviceName
     * @return array
     */
    public function logs($directory, $serviceName = null)
    {
        $args = ['-f', $directory . '/docker-compose.yml', 'logs'];
        if ($serviceName !== null) {
            $args[] = $serviceName;
        }
        return $this->composeExec($args);
    }

    /**
     * @param $containerId
     * @return array
     */
    public function inspect($containerId)
    {
        return $this->dockerExec(['inspect', '--format="{{json .}}"', $containerId]);
    }

    /**
     * @param $directory
     * @return array
     */
    public function start($directory)
    {
        return $this->composeExec(['-f', $directory . '/docker-compose.yml', 'up', '-d']);
    }

    /**
     * @param $directory
     * @return array
     */
    public function stop($directory)
    {
        return $this->composeExec(['-f', $directory . '/docker-compose.yml', 'stop']);
    }

    /**
     * @param $directory
     * @return array
     */
    public function build($directory)
    {
        return $this->composeExec(['-f', $directory . '/docker-compose.yml', 'build']);
    }

    /**
     * @param array $args
     * @return array
     */
    protected function composeExec(array $args = array())
    {
        array_unshift($args, $this->dockerComposeBin);
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
     * @param array $args
     * @return array
     */
    protected function dockerExec(array $args = array())
    {
        array_unshift($args, $this->dockerBin);
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
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
