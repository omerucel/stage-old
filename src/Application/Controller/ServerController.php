<?php

namespace Application\Controller;

use Application\Model\Permission;
use Application\Pdo\Exception\RecordNotFoundException;

class ServerController extends BaseController
{
    public function handle(array $params = [])
    {
        $this->checkPermission(Permission::PERM_PROJECT_SERVER);
        try {
            $project = $this->getMapperContainer()->getProjectMapper()
                ->findOneObjectById($this->getRequest()->get('id'));
        } catch (RecordNotFoundException $exception) {
            return $this->redirect('/projects');
        }
        $templateParams = [
            'project' => $project
        ];
        return $this->render('projects/server.twig', $templateParams);
    }
}
