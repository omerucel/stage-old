<?php

namespace Application\Project;

use Application\Docker;
use Application\Model\Project;
use Application\Nginx;

class VhostUpdater
{
    /**
     * @var string
     */
    protected $confPath;

    /**
     * @var Docker
     */
    protected $docker;

    /**
     * @var Nginx
     */
    protected $nginx;

    /**
     * @var int
     */
    protected $timeout = 15;

    /**
     * @param Docker $docker
     * @param Nginx $nginx
     * @param $confPath
     */
    public function __construct(Docker $docker, Nginx $nginx, $confPath)
    {
        $this->docker = $docker;
        $this->nginx = $nginx;
        $this->confPath = $confPath;
    }

    /**
     * @param Project $project
     */
    public function update(Project $project)
    {
        $port = $this->findHostPort($project->getDirectory(), $project->port);
        if ($port > 0) {
            $this->saveVhostFile($project, $port);
            $this->nginx->reload();
        }
    }

    /**
     * @param $directory
     * @param $port
     * @return int
     */
    protected function findHostPort($directory, $port)
    {
        $startTime = time();
        while (time() - $startTime < $this->timeout) {
            $hostPort = $this->tryListen($directory, $port);
            if ($hostPort != '') {
                return $hostPort;
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
    protected function tryListen($directory, $port)
    {
        $containers = $this->docker->getContainersInfo($directory);
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
     * @param Project $project
     * @param $hostPort
     */
    protected function saveVhostFile(Project $project, $hostPort)
    {
        $filePath = $this->confPath . '/project_' . $project->id . '.conf';
        $content = trim($project->vhost);
        $content = str_replace('$PORT$', $hostPort, $content);
        file_put_contents($filePath, $content);
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = intval($timeout);
    }
}
