<?php

namespace Application\Project\Task;

use Application\Docker;

class StopExecutor extends ExecutorAbstract implements Executor
{
    public function tryExecute()
    {
        $docker = new Docker($this->container);
        $response = $docker->stop($this->task->getProject()->getDirectory());
        $this->updateOutput(implode(PHP_EOL, $response['output']));
    }
}
