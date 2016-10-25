<?php

namespace Application\Controller;

use Application\Docker;
use Application\Pdo\Exception\RecordNotFoundException;

class ServerLogsController extends BaseController
{
    public function handle(array $params = [])
    {
        $this->getResponse()->headers->set('Content-Type', 'plain/text; charset=utf-8');
        if ($this->getUser() == null) {
            $this->getResponse()->setStatusCode(401);
            $this->getResponse()->setContent('User not found');
            return $this->getResponse();
        }
        $projectId = $this->getRequest()->get('project_id');
        if ($this->getUser()->isAllowedProject($projectId) == false) {
            $this->getResponse()->setStatusCode(403);
            $this->getResponse()->setContent('Permission denied');
            return $this->getResponse();
        }
        $serviceName = $this->getRequest()->get('service_name');
        try {
            $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($projectId);
        } catch (RecordNotFoundException $exception) {
            $this->getResponse()->setStatusCode(404);
            return $this->getResponse();
        }
        $docker = $this->getDi()->get('docker');
        $response = $docker->logs($project->getDirectory(), $serviceName);
        $this->getResponse()->setStatusCode(200);
        $this->getResponse()->setContent(implode(PHP_EOL, $response['output']));
        return $this->getResponse();
    }
}
