<?php

namespace Application\Model;

class UserActivity extends BaseModel
{
    public $id;
    public $user_id;
    public $activity;
    public $data;
    public $created_at;

    /**
     * @var User
     */
    protected $user;

    /**
     * @return User
     */
    public function getUser()
    {
        if ($this->user == null) {
            $this->user = $this->getMapperContainer()->getUserMapper()->findOneObjectById($this->user_id);
        }
        return $this->user;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'user_activity';
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->id,
            'user_id' => $this->user_id,
            'activity' => $this->activity,
            'data' => $this->data,
            'created_at' => $this->created_at
        );
    }
}
