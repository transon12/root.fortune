<?php

namespace DataApi\Model;

use Exception;
use DataApi\Model\Companies;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Orders{
    protected $_name = 'orders';
    protected $_primary = array('id');

    private $adapter = null;

    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    public function fetchAlls($options = null, $arr = null){
        $adapter = $this->adapter;
        //\Zend\Debug\Debug::dump($arr); die();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);

        if(isset($options['company_id']) && $options['company_id'] != null && $options['company_id'] != ""){
            $select->where("company_id = '" . $options['company_id'] . "'");
        }
        
        $select->where("`status` != '-1'");
        
        if(isset($options['datetime_begin'])){
            if($options['datetime_begin'] != ''){
                $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $options['datetime_begin']);
                $select->where("`created_at` >= '" . date_format($datetimeBegin, 'Y-m-d H:i:s') . "'");
            }
        }
        if(isset($options['datetime_end'])){
            if($options['datetime_end'] != ''){
                $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $options['datetime_end']);
                $select->where("`created_at` <= '" . date_format($datetimeEnd, 'Y-m-d H:i:s') . "'");
            }
        }
        if(isset($options['keyword'])){
            $select->where(" (`code` like '%".$options['keyword']."%' or
            company_id like '%".$options['keyword']."%') ");
        }
        $select->order('created_at desc');
        try{
            $selectString = $sql->buildSqlString($select);
            // echo $selectString; die();
            $results = $adapter->query($selectString,$adapter::QUERY_MODE_EXECUTE);
        }catch (Exception $e){
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
            return $results->getGeneratedValue();
        }catch(Exception $e){
            return [];
        }
        if(empty($results)){
            return [];
        }
        return false;
    }

    public function fetchRow($id){
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
            return false;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return false;
        }
        return $arr[0];
    }

    public function fetchRowAsCode($code = ""){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("code = '" . $code . "'");
        $select->limit(1);
        try {
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
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

    public function updateRow($id, $data){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $update = $sql->update($this->_name);
        $update->set($data);
        $update->where("id = '".$id."'");
        // \Zend\Debug\Debug::dump($update);die();
        try{
            $statement = $sql->prepareStatementForSqlObject($update);
            //\Zend\Debug\Debug::dump($statement) ; die();
            $results = $statement->execute();
        }catch(Exception $e){
            return false;
        }
        if(empty($results)){
            return false;
        }
        return false;
    }
}