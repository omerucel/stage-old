<?php

namespace Application\Controller;

use Application\Docker;
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
        $projectDir = $this->getConfig()->base_path . '/websites/' . $project->name;
        $docker = new Docker($this->di);
        $containers = $docker->getContainersInfo($projectDir);
        $templateParams = [
            'project' => $project,
            'containers' => $containers
        ];
        return $this->render('projects/server.twig', $templateParams);
    }
}
