<?php

namespace Settings\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;
use Storehouses\Model\Agents;
use Codes\Model\Codes;

class Messages
{
    protected $_name = 'messages';
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
        if (isset($options['keyword'])) {
            if ($options['keyword'] != '') {
                $select->where("(`code_id` like '%" . $options['keyword'] . "%' or 
                    `code_serial` like '%" . $options['keyword'] . "%' or 
                    `code_qrcode` like '%" . $options['keyword'] . "%' or 
                    `phone_id` like '%" . $options['keyword'] . "%' or 
                    `message_in` like '%" . $options['keyword'] . "%')");
            }
        }
        if (isset($options['type']) && $options['type'] != '') {
            $select->where("`type` = '" . $options['type'] . "'");
        }
        if (isset($options['status']) && $options['status'] != '') {
            if((int)$options["status"] == 2){
                $select->where("`status` >= " . $options['status'] . "");
            }else{
            $select->where("`status` = '" . $options['status'] . "'");
            }
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

        $select->order('created_at desc');
        if(isset($options["limit"])){
            $select->limit($options["limit"]);
            $select->offset((int)$options["offset"] * (int)$options["limit"]);
        }
        try {
            $selectString = $sql->buildSqlString($select);
            // echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        if (empty($results)) {
            return false;
        }
        return $results->toArray();
    }
	public function fetchAllAsCompany($companyId = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = '" . $companyId . "'");
        $select->order("id desc");
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return [];
        }
        if(empty($results)){
            return [];
        }
        return $results->toArray();
    }
	
    public function fetchCountAll($options = null)
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->columns(['total' => new Expression('count(*)')]);
        if (isset($options['company_id']) && $options['company_id'] != null) {
            $select->where("`company_id` = '" . $options['company_id'] . "'");
        }
        if (isset($options['keyword'])) {
            if ($options['keyword'] != '') {
                $select->where("(`code_id` like '%" . $options['keyword'] . "%' or 
                    `code_serial` like '%" . $options['keyword'] . "%' or 
                    `code_qrcode` like '%" . $options['keyword'] . "%' or 
                    `phone_id` like '%" . $options['keyword'] . "%' or 
                    `message_in` like '%" . $options['keyword'] . "%')");
            }
        }
        if (isset($options['type']) && $options['type'] != '') {
            $select->where("`type` = '" . $options['type'] . "'");
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
        $select->order('created_at desc');
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        if (empty($results)) {
            return false;
        }
        $re = $results->toArray();
        return $re[0]["total"];
    }

    public function fetchRow($id)
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("id = '$id'");
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        $arr = $results->toArray();
        if (empty($arr)) {
            return false;
        }
        return $arr[0];
    }

    public function fetchRowAsCodesIdCheckFirst($codesId = '', $isAgent = 0)
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("code_id = '$codesId'");
        $select->where("is_agent = '$isAgent'");
        $select->order('created_at asc');
        $select->limit(1);
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        $arr = $results->toArray();
        if (empty($arr)) {
            return false;
        }
        return $arr[0];
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

    /*public function updateRow($id, $data){
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
        if(empty($results)){
            return false;
        }
        return false;
    }
    
    public function deleteRow($id){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $delete = $sql->delete($this->_name);
        $delete->where(['id' => $id]);
        try {
            $statement = $sql->prepareStatementForSqlObject($delete);
            $results = $statement->execute();
            return $results;
        } catch (Exception $e) {
            return false;
        }
        if(empty($results)){
            return false;
        }
        return false;
    }*/

    public function fetchCount($options = null)
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->columns(['total' => new Expression('count(*)')]);
        if (isset($options['product_id'])) {
            $select->where("products_id = '" . $options['product_id'] . "'");
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
}
