<?php

namespace Application\Database\MySQL;

use Application\Pdo\Wrapper;
use League\Container\Container;

abstract class BaseMapper
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
     * @return Wrapper
     */
    public function getWrapper()
    {
        return $this->getDi()->get('pdo_wrapper');
    }

    /**
     * @return Container
     */
    public function getDi()
    {
        return $this->di;
    }
}
