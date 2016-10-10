<?php

namespace Application\Controller;

use Application\Database\MySQL\MapperContainer;
use Application\Exception\PermissionDeniedException;
use Application\Exception\UserRequiredException;
use Application\Model\User;
use Application\Pdo\Exception\RecordNotFoundException;
use League\Container\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

abstract class BaseController
{
    /**
     * @var Container
     */
    protected $di;

    /**
     * @var User
     */
    protected $user;

    /**
     * @param Container $di
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    /**
     * @param array $params
     * @return Response
     */
    abstract public function handle(array $params = []);

    /**
     * @param $permission
     * @throws PermissionDeniedException
     * @throws UserRequiredException
     */
    protected function checkPermission($permission = null)
    {
        if ($this->getUser() == null) {
            throw new UserRequiredException();
        }
        if ($permission !== null && $this->getUser()->isAllowed($permission) == false) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        if ($this->getSession()->has('app_user_id') && $this->user == null) {
            try {
                $this->user = $this->getMapperContainer()->getUserMapper()
                    ->findOneObjectById($this->getSession()->get('app_user_id'), User::STATUS_ACTIVE);
            } catch (RecordNotFoundException $exception) {
            }
        }
        return $this->user;
    }

    /**
     * @param $templateFile
     * @param array $templateParams
     * @return Response
     */
    protected function render($templateFile, array $templateParams = array())
    {
        if ($this->getUser() != null) {
            $templateParams['user'] = $this->getUser();
        }
        return $this->getResponse()->setContent($this->getTwig()->render($templateFile, $templateParams));
    }

    /**
     * @param $path
     * @return RedirectResponse
     */
    protected function redirect($path)
    {
        return new RedirectResponse($path);
    }

    /**
     * @param $name
     * @param array $data
     */
    protected function newActivity($name, array $data = [])
    {
        $this->getMapperContainer()->getUserActivityMapper()->newActivity($this->getUser()->id, $name, $data);
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return $this->getDi()->get('request');
    }

    /**
     * @return Response
     */
    protected function getResponse()
    {
        return $this->getDi()->get('response');
    }

    /**
     * @return \stdClass
     */
    protected function getConfig()
    {
        return $this->getDi()->get('config');
    }

    /**
     * @return \Twig_Environment
     */
    protected function getTwig()
    {
        return $this->getDi()->get('twig');
    }

    /**
     * @return Session
     */
    protected function getSession()
    {
        return $this->getDi()->get('session');
    }

    /**
     * @return MapperContainer
     */
    protected function getMapperContainer()
    {
        return $this->getDi()->get('mapper_container');
    }

    /**
     * @return Container
     */
    protected function getDi()
    {
        return $this->di;
    }
}
