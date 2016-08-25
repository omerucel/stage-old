<?php

namespace Application\Model;

class User extends BaseModel
{
    const STATUS_ACTIVE = 1;
    const STATUS_PASSIVE = 0;

    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    public $id;
    public $email;
    public $password;
    public $name;
    public $surname;
    public $status;
    public $role;

    /**
     * @var array
     */
    protected $permissions = array();

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role == User::ROLE_ADMIN;
    }

    /**
     * @param $permission
     * @return bool
     */
    public function isAllowed($permission)
    {
        return isset($this->getPermissions()[$permission]);
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        if (empty($this->permissions)) {
            $this->permissions = $this->getMapperContainer()->getPermissionMapper()->findAllUserPermissions($this->id);
        }
        return $this->permissions;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'users';
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
            'name' => $this->name,
            'surname' => $this->surname,
            'status' => $this->status,
            'role' => $this->role
        );
    }
}
