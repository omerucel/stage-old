<?php

namespace Application\Database\MySQL;

use Phalcon\Di;
use Application\Pdo\Wrapper;

abstract class BaseMapper
{
    /**
     * @var Di
     */
    protected $di;

    /**
     * @param Di $di
     */
    public function __construct(Di $di)
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
     * @return Di
     */
    public function getDi()
    {
        return $this->di;
    }
}
