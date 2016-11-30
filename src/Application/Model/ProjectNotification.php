<?php

namespace Application\Model;

class ProjectNotification extends BaseModel
{
    public $id;
    public $project_id;
    public $name;
    public $type;
    public $data;

    /**
     * @var ProjectNotificationSettings
     */
    protected $settings;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @return Project
     */
    public function getProject()
    {
        if ($this->project == null) {
            $this->project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($this->project_id);
        }
        return $this->project;
    }

    /**
     * @return ProjectNotificationSettings
     * @throws \Exception
     */
    public function getSettings()
    {
        if ($this->settings == null) {
            $this->settings = new ProjectNotificationSettings($this->data);
        }
        return $this->settings;
    }
}
