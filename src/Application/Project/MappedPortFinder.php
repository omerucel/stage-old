<?php

namespace Application\Project;

use Application\Command\DockerCompose;

class MappedPortFinder
{
    /**
     * @var DockerCompose
     */
    protected $dockerCompose;

    /**
     * @var int
     */
    protected $timeout = 15;

    /**
     * @param $directory
     * @param $port
     * @return mixed
     */
    public function getMappedPort($directory, $port)
    {
        $startTime = time();
        while (time() - $startTime < $this->timeout) {
            $mappedPort = $this->findInContainers($directory, $port);
            if ($mappedPort != '') {
                return $mappedPort;
            }
            sleep(3);
        }
        return null;
    }

    /**
     * @param $directory
     * @param $port
     * @return null
     */
    protected function findInContainers($directory, $port)
    {
        $containers = $this->dockerCompose->getContainersInfo($directory);
        foreach ($containers as $container) {
            if (isset($container['NetworkSettings'])
                && isset($container['NetworkSettings']['Ports'])
                && isset($container['NetworkSettings']['Ports'][$port . '/tcp'])
                && empty($container['NetworkSettings']['Ports'][$port . '/tcp']) == false
                && isset($container['NetworkSettings']['Ports'][$port . '/tcp'][0]['HostPort'])) {
                return intval($container['NetworkSettings']['Ports'][$port . '/tcp'][0]['HostPort']);
            }
        }
        return null;
    }

    /**
     * @param DockerCompose $dockerCompose
     */
    public function setDockerCompose(DockerCompose $dockerCompose)
    {
        $this->dockerCompose = $dockerCompose;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }
}
