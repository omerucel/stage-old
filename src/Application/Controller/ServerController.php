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
        $currentTask = null;
        try {
            $currentTask = $this->getMapperContainer()->getProjectTaskMapper()
                ->findCurrentSetupTaskByProject($project->id);
        } catch (RecordNotFoundException $exception) {

        }
        $templateParams = [
            'project' => $project,
            'current_task' => $currentTask
        ];
        return $this->render('projects/server.twig', $templateParams);
    }
}
