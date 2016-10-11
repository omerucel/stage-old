<?php

namespace Application;

use League\Container\Container;
use Psr\Log\LoggerInterface;

class Nginx
{
    /**
     * @var Container
     */
    protected $di;

    /**
     * @param Container $di
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    /**
     * @return array
     */
    public function restart()
    {
        return $this->nginxExec(['-s', 'reload']);
    }

    /**
     * @param array $args
     * @return array
     */
    protected function nginxExec(array $args = array())
    {
        array_unshift($args, $this->getConfig()->nginx_bin);
        $cmd = 'sudo ' . implode(' ', $args) . ' 2>&1';
        exec($cmd, $output, $exitCode);
        if ($exitCode !== 0) {
            $this->getLogger()->error($cmd, ['Output' => json_encode($output), 'ExitCode' => $exitCode]);
        }
        return [
            'output' => $output,
            'exitCode' => $exitCode
        ];
    }

    /**
     * @return \stdClass
     */
    protected function getConfig()
    {
        return $this->di->get('config');
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->di->get('logger_helper')->getLogger();
    }
}
