<?php

namespace Application\Model;

use Phalcon\Di;
use Application\Database\MySQL\MapperContainer;

abstract class BaseModel
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
     * @return MapperContainer
     */
    public function getMapperContainer()
    {
        return $this->getDi()->get('mapper_container');
    }

    /**
     * @return Di
     */
    public function getDi()
    {
        return $this->di;
    }
}
