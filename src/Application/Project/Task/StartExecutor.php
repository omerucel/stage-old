<?php

namespace Application\Project\Task;

class StartExecutor extends ExecutorAbstract implements Executor
{
    protected function tryExecute()
    {
        $response = $this->container->get('docker')->start($this->task->getProject()->getDirectory());
        $this->updateOutput(implode(PHP_EOL, $response['output']));
        $this->container->get('vhost_updater')->update($this->task->getProject());
    }
}
