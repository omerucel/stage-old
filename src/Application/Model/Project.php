<?php

namespace Application\Model;

class Project extends BaseModel
{
    public $id;
    public $name;

    /**
     * @var array
     */
    protected $files;

    /**
     * @return array
     */
    public function getFiles()
    {
        if ($this->files == null && $this->id > 0) {
            $this->files = $this->getMapperContainer()->getProjectMapper()->getProjectFiles($this->id);
        }
        return $this->files;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'project';
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
