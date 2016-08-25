<?php

namespace Application\Model;

class UserPermission extends BaseModel
{
    public $id;
    public $user_id;
    public $permission_id;

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'user_permission';
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->id,
            'user_id' => $this->user_id,
            'permission_id' => $this->permission_id
        );
    }
}
