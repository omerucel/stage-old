<?php

namespace Application\Controller;

use Application\Pdo\Exception\RecordNotFoundException;

class TaskInfoController extends BaseController
{
    public function handle(array $params = [])
    {
        $taskId = isset($params['task_id']) ? $params['task_id'] : 0;
        $this->getResponse()->headers->set('Content-Type', 'application/json; charset=utf-8');
        if ($this->getUser() == null) {
            $this->getResponse()->setStatusCode(401);
            return $this->getResponse();
        }
        $projectId = $this->getRequest()->get('project_id');
        if ($this->getUser()->isAllowedProject($projectId) == false) {
            $this->getResponse()->setStatusCode(403);
            return $this->getResponse();
        }
        try {
            $task = $this->getMapperContainer()->getProjectTaskMapper()->findOneObjectByProject($taskId, $projectId);
        } catch (RecordNotFoundException $exception) {
            $this->getResponse()->setStatusCode(403);
            return $this->getResponse();
        }
        $content = ['isCompleted' => $task->isCompleted()];
        if ($this->getRequest()->get('include_output') == 1) {
            $content['output'] = $task->output;
        }
        $this->getResponse()
            ->setStatusCode(200)
            ->setContent(json_encode($content));
        return $this->getResponse();
    }
}
