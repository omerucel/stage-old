<?php

namespace Application\Project\Task;

use Application\Docker;

class StartExecutor extends ExecutorAbstract implements Executor
{
    protected function tryExecute()
    {
        $docker = new Docker($this->container);
        $response = $docker->start($this->task->getProject()->getDirectory());
        $this->updateOutput(implode(PHP_EOL, $response['output']));
    }
}
