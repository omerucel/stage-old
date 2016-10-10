<?php

namespace Application\Controller;

use Application\Model\Permission;

class UsersController extends BaseController
{
    public function handle(array $params = [])
    {
        $this->checkPermission(Permission::PERM_USERS_LIST);
        $currentPage = intval($this->getRequest()->get('page'));
        $pager = $this->getMapperContainer()->getUserMapper()
            ->paginate(array('name' => 'asc', 'surname' => 'asc'), $currentPage);
        if ($currentPage > $pager->getLastPage()) {
            return $this->redirect('/users?page=' . $pager->getLastPage());
        }
        $templateParams = array(
            'page' => 'users',
            'pager' => $pager
        );
        return $this->render('/users/list.twig', $templateParams);
    }
}
