<?php

namespace DataApi\Model;

use DataApi\Model\OrderDetails;
use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Technologies{
    protected $_name = 'technologies';
    protected $_primary = array('id');

    private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    public function fetchAlls($orderIdNotIn = ""){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if($orderIdNotIn != ""){
            $select->where("id not in (" . $orderIdNotIn . ")");
        }
        $select->order('created_at desc');
        try{
            $selectString = $sql->buildSqlString($select);
            // echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return [];
        }
        $arr = [];
        return $results->toArray();
        // foreach($re as $item){
        //     $arr[$item['id']] = $item['name'];
        // }
        // return $arr;
    }

    public function  fetchRow($id){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("id = '" . $id . "'");
        $select->limit(1);
        try {
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return [];
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return [];
        }
        return $arr[0];
    }

    public function addRow($data){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $insert = $sql->insert($this->_name);
        $insert->values($data);
        try{
            $statement = $sql->prepareStatementForSqlObject($insert);
            $results = $statement->execute();
            return $results->getGeneratedValue();
        } catch(Exception $e){
            return [];
        }
        if(empty($results)){
            return [];
        }
        return false;
    }

    public function updateRow($id, $data){
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
            return [];
        }
        if(empty($results)){
            return [];
        }
        return false;
    }

    public function fetchAllToOption($options = null){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return [];
        }
        $re = $results->toArray();
        $arr = [];
        foreach($re as $item){
            $arr[$item['id']] = $item['name'];
        }
        return $arr;
    }

    public function fetchAllToOption1($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("status = 1");
        // remove from option if exist in order detail
        if(isset($options['orders_id'])){
            if($options['orders_id'] != ''){
                $entityOrderDetails = new OrderDetails($adapter);
                $strTechnologies = $entityOrderDetails->fetchAllAsOrderId($options['orders_id']);
                if($strTechnologies != ''){
                    $select->where("id not in (" . $strTechnologies . ")");
                }
            }
        }
        $select->order('created_at asc');
        
        try {
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        if(empty($results)){
            return false;
        }
        $arrResults = [];
        $re = $results->toArray();
        foreach($re as $item){
            $arrResults[$item['id']] = $item['name']; 
        }
        return $arrResults;
    }
}