<?php

namespace Application\Project;

use Application\Database\MySQL\ProjectTaskMapper;
use League\Container\Container;

class BackgroundTaskExecutor
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $projectId
     * @param $oldProjectDir
     * @return int
     */
    public function executeSetupTask($projectId, $oldProjectDir)
    {
        $taskId = $this->getProjectTaskMapper()->newTask($projectId, 'setup', ['old_project_dir' => $oldProjectDir]);
        $this->executeTask($taskId);
        return $taskId;
    }

    /**
     * @param $projectId
     * @return int
     */
    public function executeStartTask($projectId)
    {
        $taskId = $this->getProjectTaskMapper()->newTask($projectId, 'start');
        $this->executeTask($taskId);
        return $taskId;
    }

    /**
     * @param $projectId
     * @return int
     */
    public function executeStopTask($projectId)
    {
        $taskId = $this->getProjectTaskMapper()->newTask($projectId, 'stop');
        $this->executeTask($taskId);
        return $taskId;
    }

    /**
     * @param $taskId
     */
    protected function executeTask($taskId)
    {
        $config = $this->container->get('config');
        $cmd = [
            $config->php_bin,
            realpath($config->base_path) . '/bin/console.php',
            'task-executor:execute',
            '--id=' . $taskId
        ];
        $logFilePath = realpath($config->logger->path) . '/task_executor.log';
        static::executeBackgroundProcess($cmd, $logFilePath, ['APPLICATION_ENV' => $config->environment]);
    }

    /**
     * @param $cmd
     * @param $logFilePath
     * @param array $env
     */
    protected function executeBackgroundProcess($cmd, $logFilePath, array $env = array())
    {
        $cwd = null;
        $pipes = [];
        $spec = [
            0 => array('pipe', 'r'),
            1 => array('file', $logFilePath, 'a'),
            2 => array('file', $logFilePath, 'a')
        ];
        $cmd = implode(' ', $cmd);
        if (substr($cmd, -1) != '&') {
            $cmd.= ' &';
        }
        proc_close(proc_open($cmd, $spec, $pipes, $cwd, $env));
    }

    /**
     * @return ProjectTaskMapper
     */
    protected function getProjectTaskMapper()
    {
        return $this->container->get('mapper_container')->getProjectTaskMapper();
    }
}
