<?php

namespace Application;

use League\Container\Container;
use Psr\Log\LoggerInterface;

class Docker
{
    /**
     * @var Container
     */
    protected $di;

    /**
     * @param Container $di
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
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
                    $containers[] = json_decode($inspectResponse['output'][0]);
                }
            }
        }
        return $containers;
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
    public function restart($directory)
    {
        return $this->start($directory);
    }

    /**
     * @param $directory
     * @return array
     */
    public function start($directory)
    {
        $response = $this->stop($directory);
        if ($response['exitCode'] == 0) {
            return $this->composeExec(['-f', $directory . '/docker-compose.yml', 'up', '-d', '--build']);
        }
        return $response;
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
     * @param array $args
     * @return array
     */
    protected function composeExec(array $args = array())
    {
        array_unshift($args, $this->getConfig()->docker_compose_bin);
        $cmd = implode(' ', $args);
        exec($cmd, $output, $exitCode);
        $this->getLogger()->debug('Cmd:' . $cmd);
        $this->getLogger()->debug('Output:' . json_encode($output));
        $this->getLogger()->debug('ExitCode:' . $exitCode);
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
        array_unshift($args, $this->getConfig()->docker_bin);
        $cmd = implode(' ', $args);
        exec($cmd, $output, $exitCode);
        $this->getLogger()->debug('Cmd:' . $cmd);
        $this->getLogger()->debug('Output:' . json_encode($output));
        $this->getLogger()->debug('ExitCode:' . $exitCode);
        return [
            'output' => $output,
            'exitCode' => $exitCode
        ];
    }

    /**
     * @return \stdClass
     */
    protected function getConfig()
    {
        return $this->di->get('config');
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->di->get('logger_helper')->getLogger();
    }
}
