<?php

namespace Application\Project\Task;

use Application\Database\MySQL\ProjectTaskMapper;
use Application\Model\ProjectTask;
use League\Container\Container;
use Psr\Log\LoggerInterface;

abstract class ExecutorAbstract implements Executor
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var ProjectTask
     */
    protected $task;

    protected $lockResource;

    /**
     * @param Container $container
     * @param ProjectTask $task
     */
    public function __construct(Container $container, ProjectTask $task)
    {
        $this->container = $container;
        $this->task = $task;
    }

    abstract protected function tryExecute();

    final public function execute()
    {
        $this->lockProject();
        $this->getProjectTaskMapper()->setRunning($this->task->id);
        try {
            $isLocked = $this->lockProject();
            if ($isLocked) {
                $this->tryExecute();
            } else {
                $this->updateOutput('Project could not locked!');
            }
        } catch (\Exception $exception) {
            $this->updateOutput('An error occurred:' . $exception->getMessage());
            $this->getLogger()->error($exception);
        }
        $this->getProjectTaskMapper()->setCompleted($this->task->id);
    }

    /**
     * @return bool
     */
    private function lockProject()
    {
        $config = $this->container->get('config');
        $lockFilePath = realpath($config->base_path) . '/lock/project_' . $this->task->project_id . '.lock';
        $this->lockResource = fopen($lockFilePath, 'w');
        return flock($this->lockResource, LOCK_EX|LOCK_NB);
    }

    /**
     * @param $buffer
     */
    protected function updateOutput($buffer)
    {
        $this->task->output+= $buffer;
        $this->getProjectTaskMapper()->updateOutput($this->task->id, $buffer);
    }

    /**
     * @return ProjectTaskMapper
     */
    protected function getProjectTaskMapper()
    {
        return $this->container->get('mapper_container')->getProjectTaskMapper();
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->container->get('logger_helper')->getLogger();
    }
}