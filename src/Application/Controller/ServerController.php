<?php

namespace Application\Controller;

use Application\Pdo\Exception\RecordNotFoundException;

class ServerController extends BaseController
{
    public function handle(array $params = [])
    {
        $this->checkPermission();
        try {
            $project = $this->getMapperContainer()->getProjectMapper()
                ->findOneObjectById($this->getRequest()->get('project_id'));
            if ($this->getUser()->isAllowedProject($project->id) == false) {
                return $this->redirect('/projects');
            }
        } catch (RecordNotFoundException $exception) {
            return $this->redirect('/projects');
        }
        $templateParams = [
            'project' => $project
        ];
        return $this->render('projects/server.twig', $templateParams);
    }
}
