<?php

namespace Application\Model;

class User extends BaseModel
{
    const STATUS_ACTIVE = 1;
    const STATUS_PASSIVE = 0;

    public $id;
    public $email;
    public $password;
    public $name;
    public $surname;
    public $status;

    /**
     * @var array
     */
    protected $permissions = array();

    /**
     * @var array
     */
    protected $projects = array();

    /**
     * @param $permission
     * @return bool
     */
    public function isAllowed($permission)
    {
        return isset($this->getPermissions()[$permission]);
    }

    /**
     * @param $projectId
     * @return bool
     */
    public function isAllowedProject($projectId)
    {
        return isset($this->getProjects()[$projectId]);
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
     * @return array
     */
    public function getProjects()
    {
        if (empty($this->projects)) {
            $projects = $this->getMapperContainer()->getProjectMapper()->findAllUserProjects($this->id);
            foreach ($projects as $project) {
                $this->projects[$project->id] = $project;
            }
        }
        return $this->projects;
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
            'status' => $this->status
        );
    }
}
