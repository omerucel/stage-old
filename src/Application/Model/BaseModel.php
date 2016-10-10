<?php

namespace Application\Model;

use Application\Database\MySQL\MapperContainer;
use League\Container\Container;

abstract class BaseModel
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
     * @return MapperContainer
     */
    public function getMapperContainer()
    {
        return $this->getDi()->get('mapper_container');
    }

    /**
     * @return Container
     */
    public function getDi()
    {
        return $this->di;
    }
}
