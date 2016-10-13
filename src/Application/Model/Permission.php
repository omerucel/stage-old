<?php

namespace Application\Model;

class Permission extends BaseModel
{
    const PERM_USERS_SAVE = 'users.save';
    const PERM_USERS_LIST = 'users.list';
    const PERM_USERS_ACTIVITIES = 'users.activities';
    const PERM_PROJECT_SAVE = 'project.save';
    const PERM_PROJECT_LIST = 'project.list';

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
