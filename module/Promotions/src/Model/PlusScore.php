<?php

namespace Promotions\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class PlusScore
{
    protected $_name = 'plusscores';
//    protected $_primary = ['id'];

    private $adapter = null;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAlls()
    {
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return [];
        }
        if (empty($results)) {
            return [];
        }
        return $results->toArray();
    }
    public function deleteRow($id)
    {
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $delete = $sql->delete($this->_name);
        $delete->where(['id' => $id]);
        try {
            $statement = $sql->prepareStatementForSqlObject($delete);
            $results = $statement->execute();
        } catch (Exception $e) {
            return false;
        }
        if (empty($results)) {
            return false;
        }
        return false;
    }

    public function firstOnly($promotion_id)
    {
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select = $sql->from($this->_name);
        $select->where(['promotion_id' => $promotion_id]);
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        $arr = $results->toArray();
        if (empty($results)) {
            return false;
        }
        return $arr[0];
    }
}