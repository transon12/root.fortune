<?php

namespace Promotions\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;

class ListPromotions
{
    protected $_name = 'list_promotions';
    protected $_primary = array('id');

    private $adapter = null;
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAlls($options = null)
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        // if (isset($options['company_id'])) {
        //     $select->where("company_id = '" . $options['company_id'] . "'");
        // } else {
        //     $select->where("company_id = '" . COMPANY_ID . "'");
        // }

        // check 'keyword' exist
        if (isset($options['keyword'])) {
            $select->where("(`code_id` like '%" . $options['keyword'] . "%' or 
                `phone_id` like '%" . $options['keyword'] . "%')");
        }
        if (isset($options['phone_id'])) {
            $select->where("phone_id = '" . $options['phone_id'] . "'");
        }
        if (isset($options['datetime_begin'])) {
            if ($options['datetime_begin'] != '') {
                $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $options['datetime_begin']);
                $select->where("`created_at` >= '" . date_format($datetimeBegin, 'Y-m-d H:i:s') . "'");
            }
        }
        if (isset($options['datetime_end'])) {
            if ($options['datetime_end'] != '') {
                $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $options['datetime_end']);
                $select->where("`created_at` <= '" . date_format($datetimeEnd, 'Y-m-d H:i:s') . "'");
            }
        }
        if (isset($options['promotion_id'])) {
            if ($options['promotion_id'] != "") {
                $select->where("promotion_id = '" . $options['promotion_id'] . "'");
            }
        }
        if (isset($options['str_promotion'])) {
            $select->where("promotion_id in (" . $options['str_promotion'] . ")");
        }
        $select->order('created_at desc');
        try {
            $selectString = $sql->buildSqlString($select);
            // echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return [];
        }
        if (empty($results)) {
            return [];
        }
        return $results->toArray();
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

    public function addRow($data)
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $insert = $sql->insert($this->_name);
        $insert->values($data);
        try {
            $statement = $sql->prepareStatementForSqlObject($insert);
            $results = $statement->execute();
            return $results->getGeneratedValue();
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

    public function updateRow($id, $data)
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $update = $sql->update($this->_name);
        $update->set($data);
        $update->where(['id' => $id]);
        try {
            $statement = $sql->prepareStatementForSqlObject($update);
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

    /**
     * Danh sách các mã trúng cùng
     */
    public function fetchAllInWin($options = null)
    {
        if (!isset($options["phone_id"]) || $options["phone_id"] == "") {
            return [];
        }
        if (!isset($options["promotion_id"]) || $options["promotion_id"] == "") {
            return [];
        }
        if (!isset($options["number_winner"]) || $options["number_winner"] == "") {
            return [];
        }
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("phone_id = '" . $options["phone_id"] . "'");
        $select->where("promotion_id = '" . $options["promotion_id"] . "'");
        $select->where("number_winner = '" . $options["number_winner"] . "'");

        $select->order('id desc');
        try {
            $selectString = $sql->buildSqlString($select);
            // echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return [];
        }
        if (empty($results)) {
            return [];
        }
        return $results->toArray();
    }
}
