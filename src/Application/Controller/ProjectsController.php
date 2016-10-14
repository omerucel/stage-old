<?php

namespace Application\Controller;

use Application\Model\Permission;

class ProjectsController extends BaseController
{
    public function handle(array $params = [])
    {
        $this->checkPermission();
        if ($this->getUser()->isAllowed(Permission::PERM_PROJECT_LIST)) {
            $items = $this->getMapperContainer()->getProjectMapper()->findAll();
        } else {
            $items = $this->getUser()->getProjects();
        }
        return $this->render('/projects/list.twig', ['items' => $items]);
    }
}
