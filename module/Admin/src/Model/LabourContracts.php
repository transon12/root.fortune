<?php
namespace Admin\Model;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class LabourContracts{
    protected $_name = 'labour_contracts';
    protected $_primary = array('id');

    private $adapter=null;

    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }
    

    public function fetchAlls($userId, $options = null){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("user_id = '$userId'");
        $select->where($this->_name . ".status != '-1'");
        $select->order('created_at desc');
        try{
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return [];
        }
        if(empty($results)){
            return [];
        }
        return $results->toArray();
    }

    public function fetchRow($contractId, $userId){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("id = $contractId");
        $select->where("user_id = $userId");
        $select->limit(1);
        try {
            $selectString = $sql->buildSqlString($select);
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

    public function fetchRowAsUser($userId){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("user_id = '$userId'");
        try {
            $selectString = $sql->buildSqlString($select);
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
            return [];
        }
        return false;
    }

    public function updateRow($userId,$contractId , $data){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $update = $sql->update($this->_name);
        $update->set($data);
        $update->where(['id' => $contractId]);
        $update->where(['user_id'=>$userId]);
        try {
            $statement = $sql->prepareStatementForSqlObject($update);
            //echo $sql->buildSqlString($update); die();
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
}