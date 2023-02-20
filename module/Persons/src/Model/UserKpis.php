<?php

namespace Persons\Model;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class UserKpis{

    protected $_name = 'user_kpis';
    protected $_primary = array('id');

    private $adapter = null;

    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    public function fetchAlls($userId, $options = null){
        $year = date('Y');
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("`status` != -1");
        $select->where("user_id = $userId");
        if(!isset($options["year"]) || $options["year"] == ''){
            $select->where("`year` = $year");
        }
        if(isset($options["year"]) && $options["year"] != ''){
            $select->where("(`year` = '".$options["year"]."')");
        }
        $select->order("id desc");
        try{
            $selectString = $sql->buildSqlString($select);
        //    echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return [];
        }
        if(empty($results)){
            return [];
        }
        return $results->toArray();
    }

    public function fetchAllYears(){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where('status != -1');
        $select->order("year desc");
        
        try{
            $selectString = $sql->buildSqlString($select);
           // echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return [];
        }
        if(empty($results)){
            return [];
        }
        $re = $results->toArray();
        $arr = [];
        foreach($re as $item){
            $arr[$item['year']] = $item['year'];
        }
        return $arr;
    }

    public function addRow($data){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $insert = $sql->insert($this->_name);
        $insert->values($data);
        try {
            $statement = $sql->prepareStatementForSqlObject($insert);
            $results = $statement->execute();
            return $results;
        } catch (Exception $e) {
            return [];
        }
        if(empty($results)){
            return false;
        }
        return false;
    }

    public function fetchRowById($id, $userId){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("user_id = '$userId'");
        $select->where("id = '$id'");
        try {
            $selectString = $sql->buildSqlString($select);
           // echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return false;
        }
        return $arr[0];
    }

    public function updateRow($id, $userId, $data){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $update = $sql->update($this->_name);
        $update->set($data);
        $update->where(['user_id' => $userId]);
        $update->where(['id' => $id]);
        try {
            $statement = $sql->prepareStatementForSqlObject($update);
            // echo $sql->buildSqlString($update); die();
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
}