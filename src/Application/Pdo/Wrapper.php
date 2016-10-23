<?php

namespace Application\Pdo;

use League\Container\Container;
use Psr\Log\LoggerInterface;
use Application\Pdo\Exception\RecordNotFoundException;

class Wrapper
{
    /**
     * @var Container
     */
    protected $di;

    /**
     * @var int
     */
    protected $transactionLevel = 0;

    /**
     * @param Container $di
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    public function beginTransaction()
    {
        if ($this->transactionLevel == 0) {
            $this->getPdo()->beginTransaction();
        } else {
            $this->getPdo()->exec('SAVEPOINT LEVEL' . $this->transactionLevel);
        }
        $this->transactionLevel++;
    }

    public function rollBack()
    {
        $this->transactionLevel--;
        if ($this->transactionLevel == 0) {
            $this->getPdo()->rollBack();
        } else {
            $this->getPdo()->exec('ROLLBACK TO SAVEPOINT LEVEL' . $this->transactionLevel);
        }
    }

    public function commit()
    {
        $this->transactionLevel--;
        if ($this->transactionLevel == 0) {
            $this->getPdo()->commit();
        } else {
            $this->getPdo()->exec('RELEASE SAVEPOINT LEVEL' . $this->transactionLevel);
        }
    }

    /**
     * @param $table
     */
    public function truncate($table)
    {
        $sql = 'TRUNCATE TABLE ' . $table;
        $stmt = $this->execute($sql);
        $stmt->closeCursor();
    }

    /**
     * @param $sql
     * @param array $params
     * @return int
     */
    public function query($sql, array $params = array())
    {
        $stmt = $this->execute($sql, $params);
        $affectedRows = $stmt->rowCount();
        $stmt->closeCursor();
        return $affectedRows;
    }

    /**
     * @param $sql
     * @param array $params
     * @return int
     */
    public function insert($sql, array $params = array())
    {
        $stmt = $this->execute($sql, $params);
        $lastInsertId = $this->getPdo()->lastInsertId();
        $stmt->closeCursor();
        return $lastInsertId;
    }

    /**
     * @param $sql
     * @param array $params
     * @param int $index
     * @return mixed
     */
    public function fetchColumn($sql, array $params = array(), $index = 0)
    {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetchColumn($index);
        $stmt->closeCursor();
        return $result;
    }

    /**
     * @param $sql
     * @param array $params
     * @param int $index
     * @return mixed
     */
    public function fetchAllColumn($sql, array $params = array(), $index = 0)
    {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetchAll(\PDO::FETCH_COLUMN, $index);
        $stmt->closeCursor();
        return $result;
    }

    /**
     * @param $sql
     * @param array $params
     * @return array
     * @throws RecordNotFoundException
     */
    public function fetchOne($sql, array $params = [])
    {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if (!$result) {
            throw new RecordNotFoundException();
        }
        return $result;
    }

    /**
     * @param $sql
     * @param array $params
     * @param string $class
     * @param array $constructParams
     * @return mixed
     * @throws RecordNotFoundException
     */
    public function fetchOneObject($sql, array $params = [], $class = '\stdClass', array $constructParams = [])
    {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetchObject($class, $constructParams);
        $stmt->closeCursor();
        if (!$result) {
            throw new RecordNotFoundException();
        }
        return $result;
    }

    /**
     * @param $sql
     * @param array $params
     * @return array
     */
    public function fetchAll($sql, array $params = [])
    {
        $stmt = $this->execute($sql, $params);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $results;
    }

    /**
     * @param $sql
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function fetchAllKeyPair($sql, array $params = [])
    {
        $stmt = $this->execute($sql, $params);
        $results = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        $stmt->closeCursor();
        return $results;
    }

    /**
     * @param $sql
     * @param array $params
     * @param string $class
     * @param array $contructParams
     * @return array
     */
    public function fetchAllObjects($sql, array $params = [], $class = '\stdClass', array $contructParams = [])
    {
        $stmt = $this->execute($sql, $params);
        $results = $stmt->fetchAll(\PDO::FETCH_CLASS, $class, $contructParams);
        $stmt->closeCursor();
        return $results;
    }

    /**
     * @param $sql
     * @param array $params
     * @return \PDOStatement
     * @throws \Exception
     */
    public function execute($sql, array $params = array())
    {
        $debug = $this->getDi()->get('config')->pdo->debug;
        /**
         * @var string $sqlId
         * @var string $startTime
         */
        if ($debug) {
            $startTime = microtime(true);
        }
        try {
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute($params);
        } catch (\Exception $exception) {
            $this->logSql($startTime, $sql, $params);
            throw $exception;
        }
        if ($debug) {
            $this->logSql($startTime, $sql, $params, $stmt->rowCount());
        }
        return $stmt;
    }

    /**
     * @param $startTime
     * @param $sql
     * @param array $params
     * @param int $affectedRows
     */
    protected function logSql($startTime, $sql, array $params = array(), $affectedRows = 0)
    {
        $context = [
            'affected_rows' => $affectedRows,
            'sql_params' => $params,
            'execution_time' => number_format(microtime(true) - $startTime, 5)
        ];
        $this->getLogger()->info($sql, $context);
    }

    /**
     * @param $tableName
     * @param $params
     * @return int
     */
    public function insertWrapper($tableName, $params)
    {
        $sql = $this->createInsertSql($tableName, $params);
        return $this->insert($sql, $params);
    }

    /**
     * @param $tableName
     * @param $params
     * @return string
     */
    public function createInsertSql($tableName, $params)
    {
        $columns = array();
        $values = array();
        foreach (array_keys($params) as $key) {
            if ($this->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'mysql') {
                $columns[] = '`' . substr($key, 1) . '`';
            } elseif ($this->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'pgsql') {
                $columns[] = '"' . substr($key, 1) . '"';
            }
            $values[] = $key;
        }
        $columns = implode($columns, ', ');
        $values = implode($values, ', ');
        $sql = 'INSERT INTO ' . $tableName . '(' . $columns . ') VALUES (' . $values . ')';
        return $sql;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->getDi()->get('logger_helper')->getLogger();
    }

    /**
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->getDi()->get('pdo');
    }

    /**
     * @return Container
     */
    public function getDi()
    {
        return $this->di;
    }
}
