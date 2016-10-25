<?php

namespace Application\Controller;

use Application\Docker;

class ServerInspectController extends BaseController
{
    public function handle(array $params = [])
    {
        $this->getResponse()->headers->set('Content-Type', 'application/json; charset=utf-8');
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
        $containerId = $this->getRequest()->get('container_id');
        $docker = $this->getDi()->get('docker');
        $response = $docker->inspect($containerId);
        if ($response['exitCode'] == 0) {
            $this->getResponse()->setStatusCode(200);
            $this->getResponse()->setContent($response['output'][0]);
        } else {
            $this->getResponse()->setStatusCode(500);
        }
        return $this->getResponse();
    }
}
