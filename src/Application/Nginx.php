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
        $cmd = implode(' ', $args);
        exec($cmd, $output, $exitCode);
        $this->getLogger()->debug('Cmd:' . $cmd);
        $this->getLogger()->debug('Output:' . json_encode($output));
        $this->getLogger()->debug('ExitCode:' . $exitCode);
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
