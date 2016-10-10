<?php

namespace Application\Controller;

use Application\Model\Permission;

class UserActivitiesController extends BaseController
{
    public function handle(array $params = [])
    {
        $this->checkPermission(Permission::PERM_USERS_ACTIVITIES);
        $currentPage = intval($this->getRequest()->get('page'));
        $pager = $this->getMapperContainer()->getUserActivityMapper()
            ->paginate(array('created_at' => 'DESC'), $currentPage);
        if ($currentPage > $pager->getLastPage()) {
            return $this->redirect('/users/activities?page=' . $pager->getLastPage());
        }
        $templateParams = array(
            'page' => 'users',
            'pager' => $pager
        );
        return $this->render('/users/activities.twig', $templateParams);
    }
}
