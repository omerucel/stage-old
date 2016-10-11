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
        array_unshift($args, $this->getConfig()->docker_compose_bin);
        $cmd = implode(' ', $args) . ' 2>&1';
        exec($cmd, $output, $exitCode);
        if ($exitCode !== 0) {
            $this->getLogger()->error($cmd, ['Output' => json_encode($output), 'ExitCode' => $exitCode]);
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
        array_unshift($args, $this->getConfig()->docker_bin);
        $cmd = implode(' ', $args) . ' 2>&1';
        exec($cmd, $output, $exitCode);
        if ($exitCode !== 0) {
            $this->getLogger()->error($cmd, ['Output' => json_encode($output), 'ExitCode' => $exitCode]);
        }
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
