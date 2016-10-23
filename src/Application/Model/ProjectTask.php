<?php

namespace Application\Model;

class ProjectTask extends BaseModel
{
    const WAITING = 0;
    const RUNNING = 1;
    const COMPLETED = 2;

    public $id;
    public $project_id;
    public $name;
    public $data;
    public $status;
    public $output;
    public $created_at;
    public $updated_at;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var \stdClass
     */
    protected $dataObject;

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
     * @return \stdClass
     */
    public function getData()
    {
        if ($this->dataObject == null) {
            $this->dataObject = json_decode($this->data);
            if ($this->dataObject == null) {
                $this->dataObject = new \stdClass();
            }
        }
        return $this->dataObject;
    }
}
