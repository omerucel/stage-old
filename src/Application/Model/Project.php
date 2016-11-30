<?php

namespace Application\Model;

class Project extends BaseModel
{
    public $id;
    public $name;
    public $folder;
    public $vhost;
    public $port;
    public $public_key;

    /**
     * @var array
     */
    protected $files = [];

    /**
     * @var array
     */
    protected $notifications = [];

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->getDi()->get('config')->base_path . '/websites/' . $this->folder;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        if (empty($this->files) && $this->id > 0) {
            $this->files = $this->getMapperContainer()->getProjectMapper()->getProjectFiles($this->id);
        }
        return $this->files;
    }

    /**
     * @return array
     */
    public function getNotifications()
    {
        if (empty($this->notifications) && $this->id > 0) {
            $this->notifications = $this->getMapperContainer()->getProjectNotificationMapper()
                ->getProjectNotifications($this->id);
        }
        return $this->notifications;
    }
}
