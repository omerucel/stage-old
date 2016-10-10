<?php

namespace Application\Database\MySQL;

use League\Container\Container;

class MapperContainer
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
     * @return UserMapper
     */
    public function getUserMapper()
    {
        return $this->getMapper('UserMapper');
    }

    /**
     * @return PermissionMapper
     */
    public function getPermissionMapper()
    {
        return $this->getMapper('PermissionMapper');
    }

    /**
     * @return UserActivityMapper
     */
    public function getUserActivityMapper()
    {
        return $this->getMapper('UserActivityMapper');
    }

    /**
     * @return ProjectMapper
     */
    public function getProjectMapper()
    {
        return $this->getMapper('ProjectMapper');
    }

    /**
     * @param $name
     * @return BaseMapper
     */
    protected function getMapper($name)
    {
        $key = 'mapper_' . $name;
        if ($this->getDi()->has($key) == false) {
            $className = 'Application\Database\MySQL\\' . $name;
            $this->getDi()->share($key, new $className($this->getDi()));
        }
        return $this->getDi()->get($key);
    }

    /**
     * @return Container
     */
    public function getDi()
    {
        return $this->di;
    }
}
