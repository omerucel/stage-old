<?php

namespace Application\Controller;

class IndexController extends BaseController
{
    public function indexAction()
    {
        if ($this->getUser() == null) {
            return $this->response->redirect('/login/');
        }
        return $this->render('index.twig');
    }
}
