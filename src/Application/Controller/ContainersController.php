<?php

namespace Application\Controller;

use Application\Docker;
use Application\Pdo\Exception\RecordNotFoundException;

class ContainersController extends BaseController
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
        $docker = $this->getDi()->get('docker');
        $containers = $docker->getContainersInfo($project->getDirectory());
        $this->getResponse()->setContent(json_encode($containers));
        return $this->getResponse();
    }
}
