<?php

namespace Application\Controller;

use Application\Command\DockerCompose;
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
        /**
         * @var DockerCompose $dockerCompose
         */
        $dockerCompose = $this->getDi()->get('docker_compose');
        $process = $dockerCompose->logs($project->getDirectory(), $serviceName);
        $this->getResponse()->setStatusCode(200);
        $this->getResponse()->setContent($process->getOutput());
        return $this->getResponse();
    }
}
