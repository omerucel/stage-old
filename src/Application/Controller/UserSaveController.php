<?php

namespace Application\Controller;

use Application\Model\Permission;
use Application\Model\User;
use Application\Pdo\Exception\RecordNotFoundException;

class UserSaveController extends BaseController
{
    public function handle(array $params = [])
    {
        $this->checkPermission();
        /**
         * @var \PDO $pdo
         */
        $pdo = $this->getDi()->get('pdo');
        try {
            $pdo->beginTransaction();
            $response = $this->trySaveAction();
            $pdo->commit();
        } catch (\Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
        return $response;
    }

    protected function trySaveAction()
    {
        $id = $this->getRequest()->get('id');
        $isCurrentUser = $this->getUser()->id == $id;
        if ($isCurrentUser == false && $this->getUser()->isAllowed(Permission::PERM_USERS_SAVE) == false) {
            return $this->redirect('/');
        }
        if ($isCurrentUser) {
            $user = $this->getUser();
        } elseif ($id > 0) {
            try {
                $user = $this->getMapperContainer()->getUserMapper()->findOneObjectById($id);
            } catch (RecordNotFoundException $exception) {
                return $this->redirect('/users');
            }
        } else {
            $user = new User($this->di);
        }
        $templateParams = array(
            'form_messages' => array(),
            'id' => $id,
            'page' => $isCurrentUser ? 'settings' : 'users.save',
            'form_data' => array(
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'selected_permission_ids' => array_values($user->getPermissions()),
                'selected_project_ids' => array_keys($user->getProjects()),
                'status' => $user->status
            )
        );
        if ($this->getRequest()->isMethod('POST')) {
            $email = trim($this->getRequest()->get('email'));
            $name = trim($this->getRequest()->get('name'));
            $surname = trim($this->getRequest()->get('surname'));
            $password = trim($this->getRequest()->get('password'));
            $passwordRepeat = trim($this->getRequest()->get('password_repeat'));
            $selectedPermissionIds = $this->getRequest()->get('permissions');
            $selectedProjectIds = $this->getRequest()->get('projects');
            $status = $this->getRequest()->get('status') == 'on';
            if ($selectedPermissionIds == null) {
                $selectedPermissionIds = array();
            }
            if ($selectedProjectIds == null) {
                $selectedProjectIds = array();
            }
            $templateParams['form_data'] = array(
                'email' => $email,
                'name' => $name,
                'surname' => $surname,
                'selected_permission_ids' => $selectedPermissionIds,
                'selected_project_ids' => $selectedProjectIds,
                'status' => $status
            );

            if ($this->getMapperContainer()->getUserMapper()->isEmailUsing($email, $id) != null) {
                $templateParams['form_messages'][] = array(
                    'message' => 'Girilen e-posta adresi kullanılamaz. Lütfen başka bir e-posta adresi giriniz.',
                    'type' => 'danger'
                );
                $templateParams['has_email_error'] = true;
            }

            if ($name == '') {
                $templateParams['form_messages'][] = array(
                    'message' => 'Lütfen ad alanını boş bırakmayınız.',
                    'type' => 'danger'
                );
                $templateParams['has_name_error'] = true;
            }

            if ($surname == '') {
                $templateParams['form_messages'][] = array(
                    'message' => 'Lütfen soyad alanını boş bırakmayınız.',
                    'type' => 'danger'
                );
                $templateParams['has_surname_error'] = true;
            }

            if (strlen($password) > 0) {
                if (strlen($password) < 6) {
                    $templateParams['form_messages'][] = array(
                        'message' => 'Girilen şifre en az 6 karakter uzunluğunda olmalı.',
                        'type' => 'danger'
                    );
                }
                if ($password != $passwordRepeat) {
                    $templateParams['form_messages'][] = array(
                        'message' => 'Girilen şifre ve tekrarı eşit değil.',
                        'type' => 'danger'
                    );
                    $templateParams['has_password_error'] = true;
                }
            }

            if ($this->getUser()->isAllowed(Permission::PERM_USERS_SAVE)) {
                foreach ($selectedPermissionIds as $permissionId) {
                    if ($this->getMapperContainer()->getPermissionMapper()->hasPermissionId($permissionId) == false) {
                        $templateParams['form_messages'][] = [
                            'message' => 'Lütfen geçerli bir izin seçiniz.',
                            'type' => 'danger'
                        ];
                        $templateParams['has_permission_error'] = true;
                        break;
                    }
                }

                foreach ($selectedProjectIds as $projectId) {
                    if ($this->getMapperContainer()->getProjectMapper()->hasProjectId($projectId) == false) {
                        $templateParams['form_messages'][] = [
                            'message' => 'Lütfen geçerli bir proje seçiniz.',
                            'type' => 'danger'
                        ];
                        $templateParams['has_project_error'] = true;
                        break;
                    }
                }
            }

            if (empty($templateParams['form_messages'])) {
                $user->email = $email;
                $user->name = $name;
                $user->surname = $surname;
                if (strlen($password) > 0) {
                    $user->password = password_hash($password, PASSWORD_BCRYPT);
                }
                if ($this->getUser()->isAllowed(Permission::PERM_USERS_SAVE)) {
                    $user->status = $status;
                    $this->getMapperContainer()->getUserMapper()
                        ->updateUserProjects($user->id, $selectedProjectIds);
                    $this->getMapperContainer()->getUserMapper()
                        ->updateUserPermissions($user->id, $selectedPermissionIds);
                }
                $this->getMapperContainer()->getUserMapper()->save($user);
                $this->newActivity('users.save', array('affected_user_id' => $user->id));

                if ($isCurrentUser) {
                    $message = 'Bilgileriniz kaydedildi.';
                } else {
                    $message = 'Bilgiler kaydedildi.';
                    if ($id == 0) {
                        $templateParams['form_data'] = array(
                            'email' => '',
                            'name' => '',
                            'surname' => ''
                        );
                    }
                }
                $templateParams['form_messages'][] = array(
                    'message' => $message,
                    'type' => 'success'
                );
            }
        }
        if ($this->getUser()->isAllowed(Permission::PERM_USERS_SAVE)) {
            $templateParams['permissions'] = $this->getMapperContainer()->getPermissionMapper()->findAll();
            $templateParams['projects'] = $this->getMapperContainer()->getProjectMapper()->findAll();
        }
        return $this->render('users/save.twig', $templateParams);
    }
}
