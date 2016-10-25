<?php

namespace Application\Project\Task;

use Application\Docker;

class SetupExecutor extends ExecutorAbstract implements Executor
{
    /**
     * @var Docker
     */
    protected $docker;

    /**
     * @var bool
     */
    protected $isNewProject = false;

    /**
     * @var string
     */
    protected $newProjectDir;

    /**
     * @var string
     */
    protected $oldProjectDir;

    protected function tryExecute()
    {
        $this->prepare();
        $this->stopProject();
        $this->updateFiles();
        $this->buildAndStartProject();
        $this->container->get('vhost_updater')->update($this->task->getProject());
    }

    public function prepare()
    {
        $this->isNewProject = is_dir($this->task->getProject()->getDirectory()) == false;
        $this->docker = $this->container->get('docker');
        $this->newProjectDir = $this->task->getProject()->getDirectory();
        $this->oldProjectDir = $this->task->getData()->old_project_dir;
    }

    protected function stopProject()
    {
        if ($this->isNewProject == false) {
            $response = $this->docker->stop($this->oldProjectDir);
            $this->updateOutput(implode(PHP_EOL, $response['output']));
        }
    }

    protected function updateFiles()
    {
        if ($this->isNewProject == false
            && is_dir($this->oldProjectDir)
            && $this->oldProjectDir != $this->newProjectDir) {
            exec('mv ' . $this->oldProjectDir . ' ' . $this->newProjectDir);
        }
        if (is_dir($this->newProjectDir) == false) {
            mkdir($this->newProjectDir, 0777);
        }
        foreach ($this->task->getProject()->getFiles() as $name => $content) {
            file_put_contents($this->newProjectDir . '/' . $name, $content);
        }
    }

    protected function buildAndStartProject()
    {
        $response = $this->docker->build($this->newProjectDir);
        $this->updateOutput(implode(PHP_EOL, $response['output']));
        $response = $this->docker->start($this->newProjectDir);
        $this->updateOutput(implode(PHP_EOL, $response['output']));
    }
}
