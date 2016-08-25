<?php

namespace Application\Database\MySQL;

use Application\Pdo\Pager;

class UserActivityMapper extends BaseMapper
{
    /**
     * @param $userId
     * @param $name
     * @param array $data
     */
    public function newActivity($userId, $name, array $data = array())
    {
        $sql = 'INSERT INTO user_activity (user_id, activity, data, created_at) VALUES ('
            . ':user_id, :activity, :data, :created_at)';
        $params = [
            ':user_id' => $userId,
            ':activity' => $name,
            ':data' => json_encode($data),
            ':created_at' => date('Y-m-d H:i:s O')
        ];
        $this->getWrapper()->insert($sql, $params);
    }

    /**
     * @param array $orderItems
     * @param int $currentPage
     * @param int $perPageItem
     * @return Pager
     */
    public function paginate(array $orderItems = array(), $currentPage = 1, $perPageItem = 30)
    {
        $itemSql = 'SELECT * FROM user_activity';
        $totalCountSql = 'SELECT COUNT(*) AS count FROM user_activity';
        $pager = new Pager($this->getDi());
        $pager->setObjectClass('Application\Model\UserActivity');
        $pager->setObjectContructParams(array($this->getDi()));
        $pager->setAcceptedOrderFields(array('created_at'));
        $pager->setOrderItems($orderItems);
        $pager->setItemSql($itemSql);
        $pager->setTotalItemCountSql($totalCountSql);
        $pager->setCurrentPage($currentPage);
        $pager->setPerPageItem($perPageItem);
        return $pager;
    }
}
