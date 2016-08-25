<?php

namespace Application\Database\MySQL;

use Phalcon\Di;

class MapperContainer
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
            $this->getDi()->setShared($key, new $className($this->getDi()));
        }
        return $this->getDi()->get($key);
    }

    /**
     * @return Di
     */
    public function getDi()
    {
        return $this->di;
    }
}
