<?php

namespace Application\Command;

class DockerCompose extends CommandAbstract
{
    /**
     * @var Docker
     */
    protected $docker;

    /**
     * @param $directory
     * @return array
     */
    public function getContainersInfo($directory)
    {
        $containers = [];
        $args = ['sudo', $this->binPath, '-f', $directory . '/docker-compose.yml', 'ps', '-q'];
        $process = $this->exec($args);
        if ($process->isSuccessful()) {
            foreach (explode(PHP_EOL, trim($process->getOutput())) as $id) {
                $inspectProcess = $this->docker->inspect(trim($id));
                if ($inspectProcess->isSuccessful()) {
                    $containers[] = json_decode(trim($inspectProcess->getOutput()), true);
                }
            }
        }
        return $containers;
    }

    /**
     * @param $directory
     * @param null $serviceName
     * @return \Symfony\Component\Process\Process
     */
    public function logs($directory, $serviceName = null)
    {
        $args = ['sudo', $this->binPath, '-f', $directory . '/docker-compose.yml', 'logs'];
        if ($serviceName !== null) {
            $args[] = $serviceName;
        }
        return $this->exec($args);
    }

    /**
     * @param $directory
     * @param \Closure|null $callback
     * @return \Symfony\Component\Process\Process
     */
    public function start($directory, \Closure $callback = null)
    {
        $args = ['sudo', $this->binPath, '-f', $directory . '/docker-compose.yml', 'up', '-d'];
        return $this->exec($args, $callback);
    }

    /**
     * @param $directory
     * @param \Closure|null $callback
     * @return \Symfony\Component\Process\Process
     */
    public function stop($directory, \Closure $callback = null)
    {
        $args = ['sudo', $this->binPath, '-f', $directory . '/docker-compose.yml', 'stop'];
        return $this->exec($args, $callback);
    }

    /**
     * @param $directory
     * @param \Closure|null $callback
     * @return \Symfony\Component\Process\Process
     */
    public function build($directory, \Closure $callback = null)
    {
        $args = ['sudo', $this->binPath, '-f', $directory . '/docker-compose.yml', 'build'];
        return $this->exec($args, $callback);
    }

    /**
     * @param Docker $docker
     */
    public function setDocker(Docker $docker)
    {
        $this->docker = $docker;
    }
}
