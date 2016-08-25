<?php

namespace Application\Controller;

use Phalcon\Config;
use Phalcon\Mvc\Controller;
use Application\Database\MySQL\MapperContainer;
use Application\Model\User;
use Application\Pdo\Exception\RecordNotFoundException;

abstract class BaseController extends Controller
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @return User
     */
    protected function getUser()
    {
        if ($this->session->has('app_user_id') && $this->user == null) {
            try {
                $this->user = $this->getMapperContainer()->getUserMapper()
                    ->findOneObjectById($this->session->get('app_user_id'), User::STATUS_ACTIVE);
            } catch (RecordNotFoundException $exception) {
            }
        }
        return $this->user;
    }

    /**
     * @param $templateFile
     * @param array $templateParams
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    protected function render($templateFile, array $templateParams = array())
    {
        if ($this->getUser() != null) {
            $templateParams['user'] = $this->getUser();
        }
        return $this->response->setContent($this->getTwig()->render($templateFile, $templateParams));
    }

    /**
     * @return Config
     */
    protected function getConfig()
    {
        return $this->getDI()->get('config');
    }

    /**
     * @return \Twig_Environment
     */
    protected function getTwig()
    {
        return $this->di->get('twig');
    }

    /**
     * @return MapperContainer
     */
    protected function getMapperContainer()
    {
        return $this->getDI()->get('mapper_container');
    }
}
