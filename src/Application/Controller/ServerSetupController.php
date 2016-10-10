<?php

namespace Application\Controller;

use Application\Docker;
use Application\Model\Permission;
use Application\Pdo\Exception\RecordNotFoundException;

class ServerSetupController extends BaseController
{
    public function handle(array $params = [])
    {
        $this->checkPermission(Permission::PERM_PROJECT_SERVER_SETUP);
        $id = $this->getRequest()->get('id');
        try {
            $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($id);
        } catch (RecordNotFoundException $exception) {
            return $this->redirect('/projects');
        }
        $projectDir = $this->getConfig()->base_path . '/websites/' . $project->name;
        if (is_dir($projectDir) == false) {
            mkdir($projectDir, 0777);
        }
        foreach ($project->getFiles() as $fileName => $fileContent) {
            file_put_contents($projectDir . '/' . $fileName, $fileContent);
        }
        return $this->redirect('/projects/server?id=' . $project->id);
    }
}