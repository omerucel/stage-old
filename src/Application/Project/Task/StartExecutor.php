<?php

namespace Application\Project\Task;

use Application\Command\DockerCompose;
use Application\Project\VhostUpdater;

class StartExecutor extends ExecutorAbstract implements Executor
{
    protected function tryExecute()
    {
        $processStart = $this->getDockerCompose()->start($this->getProject()->getDirectory(), function () {
            $this->updateOutput(func_get_arg(1));
        });
        if ($processStart->isSuccessful()) {
            $this->getVhostUpdater()->update($this->getProject(), function () {
                $this->updateOutput(func_get_arg(1));
            });
        }
    }

    /**
     * @return DockerCompose
     */
    protected function getDockerCompose()
    {
        return $this->container->get('docker_compose');
    }

    /**
     * @return VhostUpdater
     */
    protected function getVhostUpdater()
    {
        return $this->container->get('vhost_updater');
    }
}
