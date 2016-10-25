<?php

namespace Application\Project\Task;

class StopExecutor extends ExecutorAbstract implements Executor
{
    public function tryExecute()
    {
        $response = $this->container->get('docker')->stop($this->task->getProject()->getDirectory());
        $this->updateOutput(implode(PHP_EOL, $response['output']));
        $filePath = realpath($this->container->get('config')->base_path)
            . '/nginx.conf.d/project_' . $this->task->getProject()->id . '.conf';
        if (is_file($filePath)) {
            unlink($filePath);
            $this->container->get('nginx')->reload();
        }
    }
}
