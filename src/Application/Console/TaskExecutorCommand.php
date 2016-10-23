<?php

namespace  Application\Console;

use Application\Project\Task\Executor;
use League\Container\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TaskExecutorCommand extends Command
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
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('task-executor:execute')->addOption('id', null, InputOption::VALUE_REQUIRED);
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var Executor $executor
         */
        $taskId = $input->getOption('id');
        $task = $this->container->get('mapper_container')->getProjectTaskMapper()->findOneObjectById($taskId);
        $class = 'Application\Project\Task\\' . ucfirst($task->name) . 'Executor';
        $executor = new $class($this->container, $task);
        $executor->execute();
    }
}
