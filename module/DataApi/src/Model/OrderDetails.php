<?php

namespace DataApi\Model;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class OrderDetails{
    protected $_name = 'order_details';

    private $adapter = null;

    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    public function fetchAll($orderId = 0){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("orders_id = '".$orderId."'");
        $select->where("status = 1");
        $select->order('created_at desc');
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return [];
        }
        if(empty($results)){
            return [];
        }
        return $results->toArray();
    }

    public function fetchAllAsOrderId($orderId = ''){
        if($orderId == '') return '';
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("orders_id = '" . $orderId . "'");
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return '';
        }
        if(empty($results)){
            return '';
        }
        $re = $results->toArray();
        $str = "";
        foreach($re as $item){
            if($str == ""){
                $str = "'" . $item['technologies_id'] . "'";
            }else{
                $str .= ", '" . $item['technologies_id'] . "'";
            }
        }
        return $str;
    }

    public function addRow($data){
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
        if(empty($results)){
            return false;
        }
        return false;
    }

    public function updateRow($id = 0, $data = []){
        $sql = new Sql($this->adapter);
        $update = $sql->update($this->_name);
        $update->set($data);
        $update->where(("id = '" . $id . "'"));
        //echo $update; die();
        try{
            $statement = $sql->prepareStatementForSqlObject($update);
           // echo $statement; die();
            $results = $statement->execute();
            return $results;
        }catch(Exception $e){
            return false;
        }
        if(empty($results)){
            return false;
        }
        return false;
    }

    // public function fetchAllAsOrdersId($options = null){
    //     if($options == null){
    //         return false;
    //     }
    //     $adapter = $this->adapter;
    //     $sql    = new Sql($this->adapter);
    //     $select = $sql->select();
    //     $select->from($this->_name);
    //     if(isset($options['orders_id'])){
    //         $select->where("orders_id = '" . $options['orders_id'] . "'");
    //     }
    //     try {
    //         $selectString = $sql->buildSqlString($select);
    //         $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
    //     } catch (Exception $e) {
    //         return false;
    //     }
    //     if(empty($results)){
    //         return false;
    //     }
    //     return $results->toArray();
    // }
}