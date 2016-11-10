<?php

namespace Application\Controller;

use Application\Pdo\Exception\RecordNotFoundException;

class TasksController extends BaseController
{
    public function handle(array $params = [])
    {
        $this->checkPermission();
        try {
            $projectId = isset($params['project_id']) ? $params['project_id'] : 0;
            $project = $this->getMapperContainer()->getProjectMapper()->findOneObjectById($projectId);
            if ($this->getUser()->isAllowedProject($project->id) == false) {
                return $this->redirect('/projects');
            }
        } catch (RecordNotFoundException $exception) {
            return $this->redirect('/projects');
        }
        $currentPage = intval($this->getRequest()->get('page'));
        $pager = $this->getMapperContainer()->getProjectTaskMapper()
            ->paginate(array('id' => 'DESC', 'created_at' => 'DESC', 'status' => 'ASC'), $currentPage);
        if ($currentPage > $pager->getLastPage()) {
            return $this->redirect('/projects/' . $projectId . './tasks?page=' . $pager->getLastPage());
        }
        $templateParams = array(
            'project' => $project,
            'page' => 'project',
            'pager' => $pager
        );
        return $this->render('projects/tasks.twig', $templateParams);
    }
}
