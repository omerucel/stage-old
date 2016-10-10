<?php

namespace Application\Controller;

use Application\Docker;
use Application\Model\Permission;

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
        if ($this->getUser()->isAllowed(Permission::PERM_PROJECT_SERVER_INSPECT) == false) {
            $this->getResponse()->setStatusCode(403);
            $this->getResponse()->setContent('Permission denied');
            return $this->getResponse();
        }
        $containerId = $this->getRequest()->get('container_id');
        $docker = new Docker($this->di);
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
