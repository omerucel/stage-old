<?php

namespace Application\Controller;

use Application\Model\User;
use Application\Pdo\Exception\RecordNotFoundException;

class LoginController extends BaseController
{
    public function indexAction()
    {
        if ($this->getUser() != null) {
            return $this->response->redirect('/');
        }
        $templateParams = array(
            'form_messages' => array()
        );
        if ($this->request->isPost()) {
            $email = $this->request->get('email');
            $password = $this->request->get('password');
            try {
                $user = $this->getMapperContainer()->getUserMapper()->findOneObjectByEmail($email, User::STATUS_ACTIVE);
                if (password_verify($password, $user->password)) {
                    $this->session->set('app_user_id', $user->id);
                    $this->getMapperContainer()->getUserActivityMapper()->newActivity($this->getUser()->id, 'login');
                    return $this->response->redirect('/');
                }
            } catch (RecordNotFoundException $exception) {
            }
            $templateParams['form_messages'][] = array(
                'message' => 'Email ya da şifre hatalı.',
                'type' => 'danger'
            );
            $templateParams['has_email_error'] = true;
            $templateParams['has_password_error'] = true;
        }
        return $this->render('login.twig', $templateParams);
    }
}
