<?php

namespace Application\Controller;

use Application\Model\User;
use Application\Pdo\Exception\RecordNotFoundException;

class LoginController extends BaseController
{
    public function handle(array $params = [])
    {
        if ($this->getUser() != null) {
            return $this->redirect('/');
        }
        $templateParams = array(
            'form_messages' => array()
        );
        if ($this->getRequest()->isMethod('POST')) {
            $email = $this->getRequest()->get('email');
            $password = $this->getRequest()->get('password');
            try {
                $user = $this->getMapperContainer()->getUserMapper()->findOneObjectByEmail($email, User::STATUS_ACTIVE);
                if (password_verify($password, $user->password)) {
                    $this->getSession()->set('app_user_id', $user->id);
                    $this->getMapperContainer()->getUserActivityMapper()->newActivity($this->getUser()->id, 'login');
                    return $this->redirect('/');
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
