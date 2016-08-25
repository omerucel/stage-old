<?php

namespace Application\Pdo;

use Phalcon\Di;

class Pager
{
    /**
     * @var Di
     */
    protected $di;

    /**
     * @var array
     */
    protected $items = null;

    /**
     * @var string
     */
    protected $objectClass = '\stdClass';

    /**
     * @var array
     */
    protected $objectContructParams = array();

    /**
     * @var string
     */
    protected $totalItemCountSql;

    /**
     * @var string
     */
    protected $itemSql;

    /**
     * @var string
     */
    protected $queryOrderSql = null;

    /**
     * @var array
     */
    protected $orderItems = array();

    /**
     * @var array
     */
    protected $acceptedOrderFields = array();

    /**
     * @var int
     */
    protected $totalItemCount = null;

    /**
     * @var int
     */
    protected $currentPage = 1;

    /**
     * @var int
     */
    protected $perPageItem = 10;

    /**
     * @param Di $di
     */
    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        if ($this->items == null) {
            $offset = $this->getPerPageItem() * ($this->getCurrentPage() - 1);
            $sql = $this->itemSql . ' ' . $this->getQueryOrder() . ' LIMIT ' . $this->getPerPageItem() . ' OFFSET '
                . $offset;
            $this->items = $this->getPdoWrapper()
                ->fetchAllObjects($sql, array(), $this->objectClass, $this->objectContructParams);
        }
        return $this->items;
    }

    /**
     * @return string
     */
    protected function getQueryOrder()
    {
        if ($this->queryOrderSql == null) {
            $this->queryOrderSql = '';
            $orderQueryParts = [];
            foreach ($this->orderItems as $orderItem => $orderType) {
                if (in_array($orderItem, $this->acceptedOrderFields)
                    && in_array($orderType, array('ASC', 'DESC'))
                ) {
                    if ($this->getPdoWrapper()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'mysql') {
                        $orderQueryParts[] = '`' . $orderItem . '` ' . $orderType;
                    } elseif ($this->getPdoWrapper()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'pgsql') {
                        $orderQueryParts[] = '"' . $orderItem . '" ' . $orderType;
                    }
                }
            }
            if (empty($orderQueryParts) == false) {
                $this->queryOrderSql = ' ORDER BY ' . implode(',', $orderQueryParts);
            }
        }
        return $this->queryOrderSql;
    }

    /**
     * @param array $acceptedOrderFields
     */
    public function setAcceptedOrderFields(array $acceptedOrderFields)
    {
        $this->acceptedOrderFields = $acceptedOrderFields;
    }

    /**
     * @param array $orderItems
     */
    public function setOrderItems(array $orderItems)
    {
        $this->orderItems = $orderItems;
    }

    /**
     * @return int
     */
    public function getTotalItemCount()
    {
        if ($this->totalItemCount == null) {
            $this->totalItemCount = $this->getPdoWrapper()->fetchColumn($this->totalItemCountSql);
        }
        return $this->totalItemCount;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage($currentPage)
    {
        if ($currentPage < 1) {
            $currentPage = 1;
        }
        $this->currentPage = $currentPage;
    }

    /**
     * @return int
     */
    public function getPerPageItem()
    {
        return $this->perPageItem;
    }

    /**
     * @param int $perPageItem
     */
    public function setPerPageItem($perPageItem)
    {
        if ($perPageItem < 1) {
            $perPageItem = 1;
        }
        $this->perPageItem = $perPageItem;
    }

    /**
     * @param string $totalItemCountSql
     */
    public function setTotalItemCountSql($totalItemCountSql)
    {
        $this->totalItemCountSql = $totalItemCountSql;
    }

    /**
     * @param string $itemSql
     */
    public function setItemSql($itemSql)
    {
        $this->itemSql = $itemSql;
    }

    /**
     * @param string $objectClass
     */
    public function setObjectClass($objectClass)
    {
        $this->objectClass = $objectClass;
    }

    /**
     * @param array $objectContructParams
     */
    public function setObjectContructParams($objectContructParams)
    {
        $this->objectContructParams = $objectContructParams;
    }

    /**
     * @return int
     */
    public function getLastPage()
    {
        return ceil($this->getTotalItemCount() / $this->getPerPageItem());
    }

    /**
     * @return Wrapper
     */
    protected function getPdoWrapper()
    {
        return $this->di->get('pdo_wrapper');
    }
}
