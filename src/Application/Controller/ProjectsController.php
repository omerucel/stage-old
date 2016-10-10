<?php

namespace Application\Controller;

use Application\Model\Permission;

class ProjectsController extends BaseController
{
    public function handle(array $params = [])
    {
        $this->checkPermission(Permission::PERM_PROJECT_LIST);
        $currentPage = intval($this->getRequest()->get('page'));
        $pager = $this->getMapperContainer()->getProjectMapper()->paginate(array('name' => 'asc'), $currentPage);
        if ($currentPage > $pager->getLastPage()) {
            return $this->redirect('/projects?page=' . $pager->getLastPage());
        }
        $templateParams = array(
            'page' => 'projects',
            'pager' => $pager
        );
        return $this->render('/projects/list.twig', $templateParams);
    }
}
