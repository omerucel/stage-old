<?php

namespace Application\Command;

use Psr\Log\LoggerAwareTrait;

abstract class CommandAbstract
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    protected $binPath;

    /**
     * @param string $binPath
     */
    public function __construct($binPath)
    {
        $this->binPath = $binPath;
    }

    /**
     * @param array $args
     * @param \Closure|null $callback
     * @return \Symfony\Component\Process\Process
     */
    protected function exec(array $args, \Closure $callback = null)
    {
        $process = CommandExecutor::exec($args, $callback);
        if ($process->isSuccessful() == false && $this->logger != null) {
            $this->logger->error(
                $process->getCommandLine(),
                [
                    'stdout' => $process->getOutput(),
                    'stderr' => $process->getErrorOutput(),
                    'exitCode' => $process->getExitCode(),
                    'exitCodeText' => $process->getExitCodeText()
                ]
            );
        }
        return $process;
    }
}
