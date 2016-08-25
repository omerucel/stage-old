<?php

namespace Application\Model;

class Permission extends BaseModel
{
    const PERM_USERS_SAVE = 'users.save';
    const PERM_USERS_LIST = 'users.list';
    const PERM_USERS_ACTIVITIES = 'users.activities';
    const PERM_PROJECT_SAVE = 'project.save';
    const PERM_PROJECT_LIST = 'project.list';
    const PERM_PROJECT_SERVER = 'project.server';
    const PERM_PROJECT_SERVER_SETUP = 'project.server.setup';
    const PERM_PROJECT_SERVER_START = 'project.server.start';
    const PERM_PROJECT_SERVER_STOP = 'project.server.stop';
    const PERM_PROJECT_SERVER_RESTART = 'project.server.restart';
    const PERM_PROJECT_SERVER_INSPECT = 'project.server.inspect';
    const PERM_PROJECT_SERVER_VHOST_GET = 'project.server.vhost.get';
    const PERM_PROJECT_SERVER_VHOST_SAVE = 'project.server.vhost.save';

    public $id;
    public $name;

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'permission';
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name
        );
    }
}
