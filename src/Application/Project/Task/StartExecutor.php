<?php

namespace Application\Project\Task;

use Application\Command\DockerCompose;
use Application\Project\VhostUpdater;

class StartExecutor extends ExecutorAbstract implements Executor
{
    protected function tryExecute()
    {
        $this->getNotificationSenderFacade()->sendProjectStarting($this->getProject());
        $processStart = $this->getDockerCompose()->start($this->getProject()->getDirectory(), function () {
            $this->updateOutput(func_get_arg(1));
        });
        if ($processStart->isSuccessful()) {
            $this->getVhostUpdater()->update($this->getProject(), function () {
                $this->updateOutput(func_get_arg(1));
            });
            $this->getNotificationSenderFacade()->sendProjectStarted($this->getProject());
        } else {
            $this->getNotificationSenderFacade()
                ->sendProjectStartFailed($this->getProject(), $processStart->getOutput());
        }
    }

    /**
     * @param \Exception $exception
     */
    protected function handleException(\Exception $exception)
    {
        parent::handleException($exception);
        $this->getNotificationSenderFacade()->sendProjectStartFailed($this->getProject(), $exception->getMessage());
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
