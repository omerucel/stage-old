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
            'name' => $this->name,
            'folder' => $this->folder,
            'vhost' => $this->vhost,
            'port' => $this->po
        );
    }
}
