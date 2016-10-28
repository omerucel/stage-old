<?php

namespace Application\Project;

use Application\Command\Nginx;
use Application\Model\Project;

class VhostUpdater
{
    /**
     * @var string
     */
    protected $confPath;

    /**
     * @var Nginx
     */
    protected $nginx;

    /**
     * @var MappedPortFinder
     */
    protected $mappedPortFinder;

    /**
     * @param Project $project
     * @param \Closure|null $callback
     */
    public function update(Project $project, \Closure $callback = null)
    {
        $mappedPort = $this->mappedPortFinder->getMappedPort($project->getDirectory(), $project->port);
        if ($mappedPort > 0) {
            $this->updateVHostFileAndReloadNginx($project, $mappedPort, $callback);
        }
    }

    /**
     * @param Project $project
     * @param $mappedPort
     * @param \Closure|null $callback
     */
    public function updateVHostFileAndReloadNginx(Project $project, $mappedPort, \Closure $callback = null)
    {
        $filePath = $this->confPath . '/project_' . $project->id . '.conf';
        $content = str_replace('$PORT$', $mappedPort, trim($project->vhost));
        file_put_contents($filePath, $content);
        $this->nginx->reload($callback);
    }

    /**
     * @param string $confPath
     */
    public function setConfPath($confPath)
    {
        $this->confPath = $confPath;
    }

    /**
     * @param Nginx $nginx
     */
    public function setNginx($nginx)
    {
        $this->nginx = $nginx;
    }

    /**
     * @param MappedPortFinder $mappedPortFinder
     */
    public function setMappedPortFinder($mappedPortFinder)
    {
        $this->mappedPortFinder = $mappedPortFinder;
    }
}
