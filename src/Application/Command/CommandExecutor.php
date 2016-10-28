<?php

namespace Application\Command;

use Symfony\Component\Process\Process;

class CommandExecutor
{
    /**
     * @param array $args
     * @param \Closure|null $callback
     * @return Process
     */
    public static function exec(array $args = [], \Closure $callback = null)
    {
        $cmd = implode(' ', $args);
        if ($callback != null) {
            call_user_func_array($callback, ['cmd', 'STAGE CMD => ' . $cmd . PHP_EOL]);
        }
        $process = new Process($cmd);
        $process->start();
        $process->wait($callback);
        return $process;
    }
}
