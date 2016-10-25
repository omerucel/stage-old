<?php

namespace Application\Controller;

use Application\Pdo\Exception\RecordNotFoundException;
use Application\Project\BackgroundTaskExecutor;

class ServerStartController extends BaseController
{
    public function handle(array $params = [])
    {
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
            $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($projectId);
        } catch (RecordNotFoundException $exception) {
            $this->getResponse()->setStatusCode(404);
            return $this->getResponse();
        }
        $taskExecutor = new BackgroundTaskExecutor($this->getDi());
        $taskId = $taskExecutor->executeStartTask($project->id);
        $this->getResponse()
            ->setStatusCode(200)
            ->setContent(json_encode(['taskId' => $taskId]));
        return $this->getResponse();
    }
}
