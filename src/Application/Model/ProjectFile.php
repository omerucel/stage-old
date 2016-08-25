<?php

namespace Application\Model;

class ProjectFile extends BaseModel
{
    public $id;
    public $project_id;
    public $name;
    public $content;

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'project_file';
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->id,
            'project_id' => $this->project_id,
            'name' => $this->name,
            'content' => $this->content
        );
    }
}
