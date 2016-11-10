<?php

namespace Application\Database\MySQL;

use Application\Model\ProjectTask;
use Application\Pdo\Pager;

class ProjectTaskMapper extends BaseMapper
{
    /**
     * @param $projectId
     * @param $name
     * @param array $data
     * @return int
     */
    public function newTask($projectId, $name, array $data = [])
    {
        return $this->getWrapper()->insertWrapper(
            'project_task',
            [
                ':project_id' => $projectId,
                ':name' => $name,
                ':data' => json_encode($data),
                ':status' => ProjectTask::WAITING,
                ':created_at' => date('Y-m-d H:i:s')
            ]
        );
    }

    /**
     * @param $taskId
     * @return ProjectTask
     */
    public function findOneObjectById($taskId)
    {
        $sql = 'SELECT * FROM project_task WHERE id =:id';
        $params = [':id' => $taskId];
        return $this->getWrapper()->fetchOneObject($sql, $params, ProjectTask::class, [$this->getDi()]);
    }

    /**
     * @param $taskId
     * @return ProjectTask
     */
    public function findOneObjectByProject($taskId, $projectId)
    {
        $sql = 'SELECT * FROM project_task WHERE id =:id AND project_id =:project_id';
        $params = [':id' => $taskId, ':project_id' => $projectId];
        return $this->getWrapper()->fetchOneObject($sql, $params, ProjectTask::class, [$this->getDi()]);
    }

    /**
     * @param $projectId
     * @return ProjectTask
     */
    public function findCurrentSetupTaskByProject($projectId)
    {
        $sql = 'SELECT * FROM project_task WHERE project_id =:project_id AND status =:status AND name = "setup"'
            . ' ORDER BY created_at DESC LIMIT 1';
        $params = [':project_id' => $projectId, ':status' => ProjectTask::RUNNING];
        return $this->getWrapper()->fetchOneObject($sql, $params, ProjectTask::class, [$this->getDi()]);
    }

    /**
     * @param $taskId
     */
    public function setRunning($taskId)
    {
        $sql = 'UPDATE project_task SET status =:status, updated_at =:updated_at WHERE id =:id';
        $params = [
            ':id' => $taskId,
            ':status' => ProjectTask::RUNNING,
            ':updated_at' => date('Y-m-d H:i:s')
        ];
        $this->getWrapper()->query($sql, $params);
    }

    /**
     * @param $taskId
     */
    public function setCompleted($taskId)
    {
        $sql = 'UPDATE project_task SET status =:status, updated_at =:updated_at WHERE id =:id';
        $params = [
            ':id' => $taskId,
            ':status' => ProjectTask::COMPLETED,
            ':updated_at' => date('Y-m-d H:i:s')
        ];
        $this->getWrapper()->query($sql, $params);
    }

    /**
     * @param $taskId
     * @param $output
     */
    public function updateOutput($taskId, $output)
    {
        $sql = 'UPDATE project_task SET output = CONCAT_WS("", output, :output), updated_at =:updated_at WHERE id =:id';
        $params = [
            ':id' => $taskId,
            ':output' => $output,
            ':updated_at' => date('Y-m-d H:i:s')
        ];
        $this->getWrapper()->query($sql, $params);
    }

    /**
     * @param array $orderItems
     * @param int $currentPage
     * @param int $perPageItem
     * @return Pager
     */
    public function paginate(array $orderItems = array(), $currentPage = 1, $perPageItem = 30)
    {
        $itemSql = 'SELECT * FROM project_task';
        $totalCountSql = 'SELECT COUNT(*) AS count FROM project_task';
        $pager = new Pager($this->getDi());
        $pager->setObjectClass(ProjectTask::class);
        $pager->setObjectContructParams(array($this->getDi()));
        $pager->setAcceptedOrderFields(array('id', 'created_at', 'status'));
        $pager->setOrderItems($orderItems);
        $pager->setItemSql($itemSql);
        $pager->setTotalItemCountSql($totalCountSql);
        $pager->setCurrentPage($currentPage);
        $pager->setPerPageItem($perPageItem);
        return $pager;
    }
}
