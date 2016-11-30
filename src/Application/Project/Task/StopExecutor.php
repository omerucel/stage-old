<?php

namespace Application\Project\Task;

use Application\Command\DockerCompose;
use Application\Command\Nginx;

class StopExecutor extends ExecutorAbstract implements Executor
{
    public function tryExecute()
    {
        $this->getNotificationSenderFacade()->sendProjectStopping($this->getProject());
        $processStop = $this->getDockerCompose()->stop($this->getProject()->getDirectory(), function () {
            $this->updateOutput(func_get_arg(1));
        });
        if ($processStop->isSuccessful()) {
            $filePath = realpath($this->getConfig()->base_path)
                . '/nginx.conf.d/project_' . $this->getProject()->id . '.conf';
            if (is_file($filePath)) {
                unlink($filePath);
                $this->getNginx()->reload(function () {
                    $this->updateOutput(func_get_arg(1));
                });
            }
            $this->getNotificationSenderFacade()->sendProjectStopped($this->getProject());
        } else {
            $this->getNotificationSenderFacade()->sendProjectStopFailed($this->getProject(), $processStop->getOutput());
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
     * @return Nginx
     */
    protected function getNginx()
    {
        return $this->container->get('nginx');
    }
}
