<?php

namespace Application\Command;

class Nginx extends CommandAbstract
{
    /**
     * @param \Closure|null $callback
     * @return \Symfony\Component\Process\Process
     */
    public function reload(\Closure $callback = null)
    {
        $args = ['sudo', $this->binPath, '-s', 'reload'];
        return $this->exec($args, $callback);
    }
}
