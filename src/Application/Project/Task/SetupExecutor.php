<?php

namespace Application\Project\Task;

use Application\Command\DockerCompose;
use Application\Project\VhostUpdater;

class SetupExecutor extends ExecutorAbstract implements Executor
{
    protected function tryExecute()
    {
        $this->getNotificationSenderFacade()->sendProjectSetupStarting($this->getProject());
        $this->stopProject();
        $this->updateFiles();
        $this->buildAndStartProject();
        $this->getNotificationSenderFacade()->sendProjectSetupFinished($this->getProject());
    }

    protected function stopProject()
    {
        if ($this->isNewProject() == false) {
            $this->getDockerCompose()->stop($this->getOldProjectDir(), function () {
                $this->updateOutput(func_get_arg(1));
            });
        }
    }

    protected function updateFiles()
    {
        if ($this->isNewProject() == false
            && is_dir($this->getOldProjectDir())
            && $this->getOldProjectDir() != $this->getProject()->getDirectory()) {
            exec('mv ' . $this->getOldProjectDir() . ' ' . $this->getProject()->getDirectory());
        }
        if (is_dir($this->getProject()->getDirectory()) == false) {
            mkdir($this->getProject()->getDirectory(), 0777);
        }
        foreach ($this->getProject()->getFiles() as $name => $content) {
            file_put_contents($this->getProject()->getDirectory() . '/' . $name, $content);
        }
    }

    protected function buildAndStartProject()
    {
        $processBuild = $this->getDockerCompose()->build($this->getProject()->getDirectory(), function () {
            $this->updateOutput(func_get_arg(1));
        });
        if ($processBuild->isSuccessful()) {
            $processStart = $this->getDockerCompose()->start($this->getProject()->getDirectory(), function () {
                $this->updateOutput(func_get_arg(1));
            });
            if ($processStart->isSuccessful()) {
                $this->getVhostUpdater()->update($this->getProject(), function () {
                        $this->updateOutput(func_get_arg(1));
                });
            }
        } else {
            $this->getNotificationSenderFacade()
                ->sendProjectSetupFailed($this->getProject(), $processBuild->getOutput());
        }
    }

    /**
     * @return bool
     */
    protected function isNewProject()
    {
        return is_dir($this->getProject()->getDirectory()) == false;
    }

    protected function handleException(\Exception $exception)
    {
        parent::handleException($exception);
        $this->getNotificationSenderFacade()->sendProjectSetupFailed($this->getProject(), $exception->getMessage());
    }

    /**
     * @return string
     */
    protected function getOldProjectDir()
    {
        return $this->task->getData()->old_project_dir;
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
