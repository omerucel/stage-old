<?php

namespace Application\Controller;

use Application\Model\Permission;
use Application\Nginx;
use Application\Pdo\Exception\RecordNotFoundException;

class VhostController extends BaseController
{
    public function handle(array $params = [])
    {
        $this->getResponse()->headers->set('Content-Type', 'application/json; charset=utf-8');
        if ($this->getUser() == null) {
            $this->getResponse()->setStatusCode(401);
            $this->getResponse()->setContent('User not found');
            return $this->getResponse();
        }
        if ($this->getRequest()->isMethod('POST')) {
            return $this->handlePost();
        } else {
            return $this->handleGet();
        }
    }

    protected function handleGet()
    {
        if ($this->getUser()->isAllowed(Permission::PERM_PROJECT_SERVER_VHOST_GET) == false) {
            $this->getResponse()->setStatusCode(403);
            $this->getResponse()->setContent('Permission denied');
            return $this->getResponse();
        }
        $id = $this->getRequest()->get('id');
        try {
            $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($id);
        } catch (RecordNotFoundException $exception) {
            $this->getResponse()->setStatusCode(404);
            return $this->getResponse();
        }
        $vhostFile = $this->getConfig()->base_path . '/nginx.conf.d/project_' . $project->id . '.conf';
        if (is_file($vhostFile)) {
            $this->getResponse()->setStatusCode(200);
            $this->getResponse()->setContent(file_get_contents($vhostFile));
        } else {
            $this->getResponse()->setStatusCode(404);
        }
        return $this->getResponse();
    }

    protected function handlePost()
    {
        if ($this->getUser()->isAllowed(Permission::PERM_PROJECT_SERVER_VHOST_SAVE) == false) {
            $this->getResponse()->setStatusCode(403);
            $this->getResponse()->setContent('Permission denied');
            return $this->getResponse();
        }
        $id = $this->getRequest()->get('id');
        $content = $this->getRequest()->get('content');
        try {
            $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($id);
        } catch (RecordNotFoundException $exception) {
            $this->getResponse()->setStatusCode(404);
            return $this->getResponse();
        }
        $vhostFile = $this->getConfig()->base_path . '/nginx.conf.d/project_' . $project->id . '.conf';
        file_put_contents($vhostFile, $content);
        $nginx = new Nginx($this->getDi());
        $response = $nginx->restart();
        if ($response['exitCode'] == 0) {
            $this->getResponse()->setStatusCode(200);
        } else {
            $this->getResponse()->setStatusCode(500);
        }
        return $this->getResponse();
    }
}
