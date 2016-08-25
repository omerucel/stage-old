<?php

namespace Application\Controller;

class LogoutController extends BaseController
{
    public function indexAction()
    {
        if ($this->getUser() != null) {
            $this->getMapperContainer()->getUserActivityMapper()->newActivity($this->getUser()->id, 'logout');
            $this->session->destroy();
        }
        return $this->response->redirect('/login');
    }
}
