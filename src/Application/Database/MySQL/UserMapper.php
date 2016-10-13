<?php

namespace Application\Database\MySQL;

use Application\Model\User;
use Application\Pdo\Exception\RecordNotFoundException;
use Application\Pdo\Pager;

class UserMapper extends BaseMapper
{
    /**
     * @param User $user
     */
    public function save(User $user)
    {
        $params = [
            ':email' => $user->email,
            ':password' => $user->password,
            ':name' => $user->name,
            ':surname' => $user->surname,
            ':status' => intval($user->status)
        ];
        if ($user->id == 0) {
            $sql = 'INSERT INTO users (email, password, name, surname, status) VALUES ('
                . ':email, :password, :name, :surname, :status)';
            $user->id = $this->getWrapper()->insert($sql, $params);
        } else {
            $sql = 'UPDATE users SET email =:email, password =:password, name =:name, surname =:surname,'
                . ' status =:status WHERE id =:id';
            $params[':id'] = $user->id;
            $this->getWrapper()->query($sql, $params);
        }
    }

    /**
     * @param $userId
     * @param array $permissions
     */
    public function updateUserPermissions($userId, array $permissions = array())
    {
        $sql = 'DELETE FROM user_permission WHERE user_id =:user_id';
        $this->getWrapper()->query($sql, [':user_id' => $userId]);
        if (empty($permissions) == false) {
            $sql = 'INSERT INTO user_permission (user_id, permission_id) VALUES '
                . substr(str_repeat('(?, ?),', count($permissions)), 0, -1);
            $params = array();
            foreach ($permissions as $permissionId) {
                $params[] = $userId;
                $params[] = $permissionId;
            }
            $this->getWrapper()->query($sql, $params);
        }
    }

    /**
     * @param $userId
     * @param array $projects
     */
    public function updateUserProjects($userId, array $projects = array())
    {
        $sql = 'DELETE FROM user_project WHERE user_id =:user_id';
        $this->getWrapper()->query($sql, [':user_id' => $userId]);
        if (empty($projects) == false) {
            $sql = 'INSERT INTO user_project (user_id, project_id) VALUES '
                . substr(str_repeat('(?, ?),', count($projects)), 0, -1);
            $params = array();
            foreach ($projects as $projectId) {
                $params[] = $userId;
                $params[] = $projectId;
            }
            $this->getWrapper()->query($sql, $params);
        }
    }

    /**
     * @param $id
     * @param null $status
     * @return User
     * @throws RecordNotFoundException
     */
    public function findOneObjectById($id, $status = null)
    {
        $sql = 'SELECT * FROM users WHERE id =:id';
        $params = [':id' => $id];
        if ($status !== null) {
            $sql.= ' AND status =:status';
            $params[':status'] = $status;
        }
        return $this->getWrapper()
            ->fetchOneObject($sql, $params, 'Application\Model\User', [$this->getDi()]);
    }

    /**
     * @param $email
     * @param null $status
     * @return User
     * @throws RecordNotFoundException
     */
    public function findOneObjectByEmail($email, $status = null)
    {
        $sql = 'SELECT * FROM users WHERE email =:email';
        $params = [':email' => $email];
        if ($status !== null) {
            $sql.= ' AND status =:status';
            $params[':status'] = $status;
        }
        return $this->getWrapper()
            ->fetchOneObject($sql, $params, 'Application\Model\User', [$this->getDi()]);
    }

    /**
     * @param $email
     * @param null $excludedId
     * @return bool
     */
    public function isEmailUsing($email, $excludedId = null)
    {
        $sql = 'SELECT COUNT(id) FROM users WHERE email =:email';
        $params = [':email' => $email];
        if ($excludedId > 0) {
            $sql.= ' AND id !=:id';
            $params[':id'] = $excludedId;
        }
        return $this->getWrapper()->fetchColumn($sql, $params) > 0;
    }

    /**
     * @return array
     */
    public function findAll()
    {
        $sql = 'SELECT * FROM users ORDER BY name ASC, surname ASC';
        return $this->getWrapper()
            ->fetchAllObjects($sql, [], 'Application\Model\User', [$this->getDi()]);
    }

    /**
     * @param array $orderItems
     * @param int $currentPage
     * @param int $perPageItem
     * @return Pager
     */
    public function paginate(array $orderItems = array(), $currentPage = 1, $perPageItem = 30)
    {
        $itemSql = 'SELECT * FROM users';
        $totalCountSql = 'SELECT COUNT(*) AS count FROM users';
        $pager = new Pager($this->getDi());
        $pager->setObjectClass('Application\Model\User');
        $pager->setObjectContructParams(array($this->getDi()));
        $pager->setAcceptedOrderFields(array('name', 'surname'));
        $pager->setOrderItems($orderItems);
        $pager->setItemSql($itemSql);
        $pager->setTotalItemCountSql($totalCountSql);
        $pager->setCurrentPage($currentPage);
        $pager->setPerPageItem($perPageItem);
        return $pager;
    }
}
