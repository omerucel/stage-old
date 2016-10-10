<?php

namespace Application\Controller;

use Application\Docker;
use Application\Model\Permission;
use Application\Pdo\Exception\RecordNotFoundException;

class ServerStartController extends BaseController
{
    public function handle(array $params = [])
    {
        $this->getResponse()->headers->set('Content-Type', 'application/json; charset=utf-8');
        if ($this->getUser() == null) {
            $this->getResponse()->setStatusCode(401);
            return $this->getResponse();
        }
        if ($this->getUser()->isAllowed(Permission::PERM_PROJECT_SERVER_START) == false) {
            $this->getResponse()->setStatusCode(403);
            return $this->getResponse();
        }
        $id = $this->getRequest()->get('id');
        try {
            $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($id);
        } catch (RecordNotFoundException $exception) {
            $this->getResponse()->setStatusCode(404);
            return $this->getResponse();
        }
        $projectDir = $this->getConfig()->base_path . '/websites/' . $project->name;
        $docker = new Docker($this->di);
        $response = $docker->start($projectDir);
        if ($response['exitCode'] == 0) {
            $this->getResponse()->setStatusCode(200);
        } else {
            $this->getResponse()->setStatusCode(500);
        }
        return $this->getResponse();
    }
}
