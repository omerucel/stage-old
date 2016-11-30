<?php

namespace Application\Database\MySQL;

use Application\Model\Project;
use Application\Pdo\Exception\RecordNotFoundException;
use Application\Pdo\Pager;

class ProjectMapper extends BaseMapper
{
    /**
     * @param Project $project
     */
    public function save(Project $project)
    {
        $params = [
            ':name' => $project->name,
            ':folder' => $project->folder,
            ':vhost' => $project->vhost,
            ':port' => $project->port,
            ':public_key' => $project->public_key
        ];
        if ($project->id == 0) {
            $sql = 'INSERT INTO project (name, folder, vhost, port, public_key)'
                . ' VALUES (:name, :folder, :vhost, :port, :public_key)';
            $project->id = $this->getWrapper()->insert($sql, $params);
        } else {
            $sql = 'UPDATE project SET name =:name, folder =:folder, vhost =:vhost, port =:port'
                . ', public_key =:public_key WHERE id =:id';
            $params[':id'] = $project->id;
            $this->getWrapper()->query($sql, $params);
        }
    }

    /**
     * @param $projectId
     * @param array $files
     */
    public function updateProjectFiles($projectId, array $files = array())
    {
        $sql = 'DELETE FROM project_file WHERE project_id =:project_id';
        $this->getWrapper()->query($sql, [':project_id' => $projectId]);
        if (empty($files) == false) {
            $sql = 'INSERT INTO project_file (project_id, name, content) VALUES '
                . substr(str_repeat('(?, ?, ?),', count($files)), 0, -1);
            $params = array();
            foreach ($files as $file) {
                $params[] = $projectId;
                $params[] = $file['name'];
                $params[] = $file['content'];
            }
            $this->getWrapper()->query($sql, $params);
        }
    }

    /**
     * @param $projectId
     * @param array $notifications
     */
    public function updateProjectNotifications($projectId, array $notifications = array())
    {
        $sql = 'DELETE FROM project_notification WHERE project_id =:project_id';
        $this->getWrapper()->query($sql, [':project_id' => $projectId]);
        if (empty($notifications) == false) {
            $sql = 'INSERT INTO project_notification (project_id, name, type, data) VALUES '
                . substr(str_repeat('(?, ?, ?, ?),', count($notifications)), 0, -1);
            $params = array();
            foreach ($notifications as $notification) {
                $params[] = $projectId;
                $params[] = $notification['name'];
                $params[] = $notification['type'];
                $params[] = json_encode($notification['data']);
            }
            $this->getWrapper()->query($sql, $params);
        }
    }

    /**
     * @param $userId
     * @return array
     */
    public function findAllUserProjects($userId)
    {
        $sql = 'SELECT project.* FROM project'
            . ' INNER JOIN user_project ON user_project.project_id = project.id'
            . ' WHERE user_project.user_id =:user_id';
        $params = [':user_id' => $userId];
        return $this->getWrapper()->fetchAllObjects($sql, $params, 'Application\Model\Project', [$this->getDi()]);
    }

    /**
     * @return array
     */
    public function findAll()
    {
        $sql = 'SELECT * FROM project ORDER BY name ASC';
        return $this->getWrapper()->fetchAll($sql);
    }

    /**
     * @param $id
     * @return Project
     * @throws RecordNotFoundException
     */
    public function findOneObjectById($id)
    {
        $sql = 'SELECT * FROM project WHERE id =:id';
        $params = [':id' => $id];
        return $this->getWrapper()
            ->fetchOneObject($sql, $params, 'Application\Model\Project', [$this->getDi()]);
    }

    /**
     * @param $publicKey
     * @return Project
     * @throws RecordNotFoundException
     */
    public function findOneObjectByPublicKey($publicKey)
    {
        $sql = 'SELECT * FROM project WHERE public_key =:public_key';
        $params = [':public_key' => $publicKey];
        return $this->getWrapper()->fetchOneObject($sql, $params, Project::class, [$this->getDi()]);
    }

    /**
     * @param $name
     * @param null $excludedId
     * @return bool
     */
    public function isProjectNameUsing($name, $excludedId = null)
    {
        $sql = 'SELECT COUNT(id) FROM project WHERE name =:name';
        $params = [':name' => $name];
        if ($excludedId > 0) {
            $sql.= ' AND id !=:id';
            $params[':id'] = $excludedId;
        }
        return $this->getWrapper()->fetchColumn($sql, $params) > 0;
    }

    /**
     * @param $publicKey
     * @param null $excludedId
     * @return bool
     */
    public function isPublicKeyUsing($publicKey, $excludedId = null)
    {
        $sql = 'SELECT COUNT(id) FROM project WHERE public_key =:public_key';
        $params = [':public_key' => $publicKey];
        if ($excludedId > 0) {
            $sql.= ' AND id !=:id';
            $params[':id'] = $excludedId;
        }
        return $this->getWrapper()->fetchColumn($sql, $params) > 0;
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasProjectId($id)
    {
        $sql = 'SELECT COUNT(id) FROM project WHERE id =:id';
        $params = [':id' => $id];
        return $this->getWrapper()->fetchColumn($sql, $params) > 0;
    }

    /**
     * @param $projectId
     * @return array
     */
    public function getProjectFiles($projectId)
    {
        $sql = 'SELECT name, content FROM project_file WHERE project_id =:project_id ORDER BY name ASC';
        $params = [':project_id' => $projectId];
        return $this->getWrapper()->fetchAllKeyPair($sql, $params);
    }

    /**
     * @param array $orderItems
     * @param int $currentPage
     * @param int $perPageItem
     * @return Pager
     */
    public function paginate(array $orderItems = array(), $currentPage = 1, $perPageItem = 30)
    {
        $itemSql = 'SELECT * FROM project';
        $totalCountSql = 'SELECT COUNT(*) AS count FROM project';
        $pager = new Pager($this->getDi());
        $pager->setObjectClass('Application\Model\Project');
        $pager->setObjectContructParams(array($this->getDi()));
        $pager->setAcceptedOrderFields(array('name'));
        $pager->setOrderItems($orderItems);
        $pager->setItemSql($itemSql);
        $pager->setTotalItemCountSql($totalCountSql);
        $pager->setCurrentPage($currentPage);
        $pager->setPerPageItem($perPageItem);
        return $pager;
    }
}
