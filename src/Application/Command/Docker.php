<?php

namespace Application\Command;

class Docker extends CommandAbstract
{
    /**
     * @param $containerId
     * @return \Symfony\Component\Process\Process
     */
    public function inspect($containerId)
    {
        $args = ['sudo', $this->binPath, 'inspect', '--format="{{json .}}"', $containerId];
        return $this->exec($args);
    }
}
