<?php

namespace Persons\Model;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Recruitments{

    protected $_name = 'recruitments';
    protected $_primary = array('id');

    private $adapter = null;

    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    public function fetchAlls($options = null){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("status = '1'");
        if(isset($options['keyword'])){
            $select->where(" department like '%".$options['keyword']."%'");
        }
        $select->order('created_at desc');
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
        return $results->toArray();
    }

    public function fetchRow($id){
        $adapter = $this->adapter;
        
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
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
            return false;
        }
        if(empty($results)){
            return false;
        }
        return false;
    }
}