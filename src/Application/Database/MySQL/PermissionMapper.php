<?php

namespace Application\Database\MySQL;

class PermissionMapper extends BaseMapper
{
    /**
     * @param $userId
     * @return array
     */
    public function findAllUserPermissions($userId)
    {
        $sql = 'SELECT permission.name, permission.id FROM permission'
            . ' INNER JOIN user_permission ON user_permission.permission_id = permission.id'
            . ' WHERE user_permission.user_id =:user_id';
        $params = [':user_id' => $userId];
        return $this->getWrapper()->fetchAllKeyPair($sql, $params);
    }

    /**
     * @return array
     */
    public function findAll()
    {
        $sql = 'SELECT name, id FROM permission ORDER BY name ASC';
        return $this->getWrapper()->fetchAll($sql);
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasPermissionId($id)
    {
        $sql = 'SELECT COUNT(id) FROM permission WHERE id =:id';
        $params = [':id' => $id];
        return $this->getWrapper()->fetchColumn($sql, $params) > 0;
    }
}
