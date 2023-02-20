<?php

namespace Companies\Model;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Surrogates{
    protected $_name = 'surrogates';
    protected $_primary = array('id');

    private $adapter = null;

    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    public function fetchAll($options = null,$companyId){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = '".$companyId."'");
        $select->where("status != -1");
        if(isset($options['keyword'])){
            $select->where("phone like '%".$options['keyword']."%' or
            name like '%".$options['keyword']."%'");
        }
        $select->order('created_at desc');
        try{
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
            $results = $adapter->query($selectString,$adapter::QUERY_MODE_EXECUTE);

        }catch(Exception $e){
            return [];
        }
        if(empty($results)){
            return [];
        }
        return $results->toArray();
    }

    public function fetchRow($id,$companyId){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = '".$companyId."'");
        $select->where("id = $id");
        $select->limit(1);
        try{
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
            $results = $adapter->query($selectString,$adapter::QUERY_MODE_EXECUTE);

        }catch(Exception $e){
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
        }catch(Exception $e){
            return [];
        }
        if(empty($results)){
            return [];
        }
        return false;
    }

    public function updateRow($data,$id,$companyId){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $update = $sql->update($this->_name);
        $update->set($data);
        $update->where("id = $id and company_id = '".$companyId."'");
        try{
            $statement = $sql->prepareStatementForSqlObject($update);
            //\Zend\Debug\Debug::dump($statement) ; die();
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

    public function fetchAllToOptions($options = null){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = '".$options."'");
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
        $arrResults = [];
        $entityPositions = new \Admin\Model\Positions($adapter);
        $arrPositions = $entityPositions->fetchAllOptions();
        $re = $results->toArray();
        //\Zend\Debug\Debug::dump($re); die();
        foreach($re as $item){
            $arrResults[$item['id']] = $arrPositions[$item['positions_id']] . " - " . $item['name'];
        }
        return $arrResults;
    }
}