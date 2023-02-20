<?php

namespace Companies\Model;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class MissionDetails{
    protected $_name = 'mission_details';

    private $adapter = null;

    public static function getStatus(){
	    return [
	        '0' => 'Quá thời gian nhưng chưa hoàn thành',
	        '1' => 'Đang thực hiện',
	        '2' => 'Hoàn thành',
            '3' => 'Hoàn thành trễ',
	        '-1' => 'Đã xóa'
	    ];
	}

    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    public function fetchAll($orderId){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("order_id = '".$orderId."'");
        $select->where("status != -1");
        $select->order("created_at asc");
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

    public function addRow($data){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $insert = $sql->insert($this->_name);
        $insert->values($data);
        try{
            $statement = $sql->prepareStatementForSqlObject($insert);
            $results = $statement->execute();
        }catch(Exception $e){
            return [];
        }
        if(empty($results)){
            return [];
        }
        return false;
    }

    public function fetchRow($orderId,$detailId){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("id = '".$detailId."'");
        $select->where("order_id = '".$orderId."'");
        $select->limit(1);
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return [];
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return false;
        }
        return $arr[0];
    }

    public function fetchRowByMissionId($orderId,$missionId){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("mission_id = '".$missionId."'");
        $select->where("order_id = '".$orderId."'");
        $select->limit(1);
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return [];
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return false;
        }
        return $arr[0];
    }

    public function updateRow($data, $options = null){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $update = $sql->update($this->_name);
        $update->set($data);
        if(isset($options['id'])){
            $update->where("id = '".$options['id']."'");
        }
        if(isset($options['order_id'])){
            $update->where("order_id = '".$options['order_id']."'");
        }
        try{
            $statement = $sql->prepareStatementForSqlObject($update);
            $results = $statement->execute();
        }catch(Exception $e){
            return false;
        }
        if(empty($results)){
            return false;
        }
        return false;
    }

    public function fetchRowAsUsers($username){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        try{
            $selectString = $sql->buildSqlString($select);
           // echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return [];
        }
        $arr = [];
        $order= [];
        $res = $results->toArray();
        $i= 0;
        // \Zend\Debug\Debug::dump($res); die();
        foreach($res as $item){
            $arr[$i] = explode(', ',$item['user']);
            if(in_array($username,$arr[$i])){
                $order[$item['order_id']] = $item['order_id'];
            }
            $i++;
        }
        // \Zend\Debug\Debug::dump($order);die();
        // \Zend\Debug\Debug::dump($arr); die();
        return $order;
    }
}