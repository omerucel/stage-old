<?php

namespace Application\Controller;

class LogoutController extends BaseController
{
    public function handle(array $params = [])
    {
        if ($this->getUser() != null) {
            $this->newActivity('logout');
            $this->getSession()->clear();
        }
        return $this->redirect('/login');
    }
}
