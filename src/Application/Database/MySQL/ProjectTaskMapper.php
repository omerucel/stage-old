<?php

namespace Application\Database\MySQL;

use Application\Model\ProjectTask;

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
     * @param $command
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
}