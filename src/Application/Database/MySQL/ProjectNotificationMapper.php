<?php

namespace Application\Database\MySQL;

use Application\Model\ProjectNotification;

class ProjectNotificationMapper extends BaseMapper
{
    /**
     * @param $projectId
     * @return array
     */
    public function getProjectNotifications($projectId)
    {
        $sql = 'SELECT * FROM project_notification WHERE project_id =:project_id';
        $params = [':project_id' => $projectId];
        return $this->getWrapper()->fetchAllObjects($sql, $params, ProjectNotification::class, [$this->getDi()]);
    }
}
