<?php

namespace Application\Controller;

use Application\Model\Permission;
use Application\Model\User;
use Application\Pdo\Exception\RecordNotFoundException;

class UsersController extends BaseController
{
    public function indexAction()
    {
        if ($this->getUser() == null) {
            return $this->response->redirect('/login/');
        }
        if ($this->getUser()->isAllowed(Permission::PERM_USERS_LIST) == false) {
            return $this->response->redirect('/');
        }
        $currentPage = intval($this->request->get('page'));
        $pager = $this->getMapperContainer()->getUserMapper()
            ->paginate(array('name' => 'asc', 'surname' => 'asc'), $currentPage);
        if ($currentPage > $pager->getLastPage()) {
            return $this->response->redirect('/users?page=' . $pager->getLastPage());
        }
        $templateParams = array(
            'page' => 'users',
            'pager' => $pager
        );
        return $this->render('/users/list.twig', $templateParams);
    }

    public function activitiesAction()
    {
        if ($this->getUser() == null) {
            return $this->response->redirect('/login/');
        }
        if ($this->getUser()->isAllowed(Permission::PERM_USERS_ACTIVITIES) == false) {
            return $this->response->redirect('/');
        }
        $currentPage = intval($this->request->get('page'));
        $pager = $this->getMapperContainer()->getUserActivityMapper()
            ->paginate(array('created_at' => 'DESC'), $currentPage);
        if ($currentPage > $pager->getLastPage()) {
            return $this->response->redirect('/users/activities?page=' . $pager->getLastPage());
        }
        $templateParams = array(
            'page' => 'users',
            'pager' => $pager
        );
        return $this->render('/users/activities.twig', $templateParams);
    }

    public function saveAction()
    {
        /**
         * @var \PDO $pdo
         */
        $pdo = $this->getDI()->get('pdo');
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
        if ($this->getUser() == null) {
            return $this->response->redirect('/login/');
        }
        $id = $this->request->get('id');
        $isCurrentUser = $this->getUser()->id == $id;
        if ($isCurrentUser == false && $this->getUser()->isAllowed(Permission::PERM_USERS_SAVE) == false) {
            return $this->response->redirect('/');
        }
        if ($isCurrentUser) {
            $user = $this->getUser();
        } elseif ($id > 0) {
            try {
                $user = $this->getMapperContainer()->getUserMapper()->findOneObjectById($id);
            } catch (RecordNotFoundException $exception) {
                return $this->response->redirect('/users');
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
                'role' => $user->role,
                'selected_permission_ids' => array_values($user->getPermissions()),
                'status' => $user->status
            )
        );
        if ($this->request->isPost()) {
            $email = trim($this->request->get('email'));
            $name = trim($this->request->get('name'));
            $surname = trim($this->request->get('surname'));
            $role = trim($this->request->get('role'));
            $password = trim($this->request->get('password'));
            $passwordRepeat = trim($this->request->get('password_repeat'));
            $selectedPermissionIds = $this->request->get('permissions');
            $status = $this->request->get('status') == 'on';
            if ($selectedPermissionIds == null) {
                $selectedPermissionIds = array();
            }
            $templateParams['form_data'] = array(
                'email' => $email,
                'name' => $name,
                'surname' => $surname,
                'role' => $role,
                'selected_permission_ids' => $selectedPermissionIds,
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

            if ($this->getUser()->isAdmin()) {
                if (in_array($role, array(User::ROLE_ADMIN, User::ROLE_USER)) == false) {
                    $templateParams['form_messages'][] = array(
                        'message' => 'Lütfen geçerli bir rol seçiniz.',
                        'type' => 'danger'
                    );
                    $templateParams['has_role_error'] = true;
                }
                if ($this->getUser()->isAdmin() && $isCurrentUser && $role != User::ROLE_ADMIN) {
                    $templateParams['form_messages'][] = array(
                        'message' => 'Rolünüzü değiştiremezsiniz.',
                        'type' => 'danger'
                    );
                    $templateParams['has_role_error'] = true;
                }
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
            }

            if (empty($templateParams['form_messages'])) {
                $user->email = $email;
                $user->name = $name;
                $user->surname = $surname;
                if (strlen($password) > 0) {
                    $user->password = password_hash($password, PASSWORD_BCRYPT);
                }
                if ($this->getUser()->isAdmin()) {
                    $user->role = $role;
                }
                if ($this->getUser()->isAdmin() && $isCurrentUser == false) {
                    $user->status = $status;
                }
                $this->getMapperContainer()->getUserMapper()->save($user);
                $this->getMapperContainer()->getUserMapper()->updateUserPermissions($user->id, $selectedPermissionIds);
                $this->getMapperContainer()->getUserActivityMapper()->newActivity(
                    $this->getUser()->id,
                    'users.save',
                    array('affected_user_id' => $user->id)
                );

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
        if ($this->getUser()->isAdmin()) {
            $templateParams['permissions'] = $this->getMapperContainer()->getPermissionMapper()->findAll();
        }
        return $this->render('users/save.twig', $templateParams);
    }
}
