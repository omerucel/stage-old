<?php

namespace Application\Controller;

class IndexController extends BaseController
{
    public function handle(array $params = [])
    {
        $this->checkPermission();
        return $this->redirect('/projects');
    }
}
