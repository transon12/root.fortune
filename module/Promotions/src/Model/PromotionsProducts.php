<?php

namespace Promotions\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;

class PromotionsProducts
{
    protected $_name = 'promotions_products';
    protected $_primary = ['promotion_id', 'product_id'];

    private $adapter = null;
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function addRow($data)
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $insert = $sql->insert($this->_name);
        $insert->values($data);
        try {
            $statement = $sql->prepareStatementForSqlObject($insert);
            $results = $statement->execute();
            return $results;
        } catch (Exception $e) {
            return false;
        }
        if (empty($results)) {
            return false;
        }
        return false;
    }

    public function fetchCount($options = null)
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->columns(['total' => new Expression('count(*)')]);
        if (isset($options['promotion_id'])) {
            $select->where("promotion_id = '" . $options['promotion_id'] . "'");
        }
        if (isset($options['product_id'])) {
            $select->where("product_id = '" . $options['product_id'] . "'");
        }
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return 0;
        }
        $arr = $results->toArray();
        if (empty($arr)) {
            return 0;
        }
        return $arr[0]['total'];
    }

    /**
     * Get all data as promotions_id
     */
    public function fetchAllAsPromotionId($promotionId = 0, $toOption = false)
    {
        if ($promotionId == 0) {
            return false;
        }
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("promotion_id = '$promotionId'");
        $select->order('created_at asc');
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        if (empty($results)) {
            return false;
        }
        if (!$toOption) {
            return $results->toArray();
        } else {
            $re = [];
            foreach ($results->toArray() as $item) {
                $re[$item['product_id']] = $item['score'];
            }
            return $re;
        }
    }

    /**
     * Get all data as promotions_id
     */
    public function fetchAllOptionKeyPromotion()
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("score != 0");
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        if (empty($results)) {
            return false;
        }
        $re = [];
        foreach ($results->toArray() as $item) {
            $re[$item['promotion_id']] = $item;
        }
        return $re;
    }

    /**
     * Get all data as promotions_id
     */
    public function fetchAllOptionKeyProduct()
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("score != 0");
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        if (empty($results)) {
            return false;
        }
        $re = [];
        foreach ($results->toArray() as $item) {
            $re[$item['product_id']] = $item;
        }
        return $re;
    }

    public function deleteRowsAsPromotionsId($promotionsId = null)
    {
        if ($promotionsId == null) return false;
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $delete = $sql->delete($this->_name);
        $delete->where(['promotion_id' => $promotionsId]);
        try {
            $statement = $sql->prepareStatementForSqlObject($delete);
            $results = $statement->execute();
            return $results;
        } catch (Exception $e) {
            return false;
        }
        if (empty($results)) {
            return false;
        }
        return false;
    }

    public function deleteRows($conditions = [])
    {
        if (empty($conditions)) return false;
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $delete = $sql->delete($this->_name);
        if (isset($conditions['promotion_id'])) {
            $delete->where(['promotion_id' => $conditions['promotion_id']]);
        }
        if (isset($conditions['product_id'])) {
            $delete->where(['product_id' => $conditions['product_id']]);
        }
        try {
            $statement = $sql->prepareStatementForSqlObject($delete);
            $results = $statement->execute();
            return $results;
        } catch (Exception $e) {
            return false;
        }
        if (empty($results)) {
            return false;
        }
        return false;
    }
}
