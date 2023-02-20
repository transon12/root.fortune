<?php

namespace Settings\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Companies{
	protected $_name = 'companies';
	protected $_primary = array('id');
	
	private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }
    
    public function fetchAll($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if($options['id'] != 1){
            $select->where("user_id = '".$options['id']."'");
        }
        if(isset($options['keyword'])){
            $select->where("(`name` like '%" . $options['keyword'] . "%' or
            id like '%" . $options['keyword'] ."%')");
        }
        $select->order('created_at desc');
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
        $re = $results->toArray();
        $res = [];
        $entityWards = new Wards($adapter);
        $i = 0;
        foreach($re as $item){
            $res[$i] = $item;
            $res[$i]['full_address'] = $item['address'] . $entityWards->fetchFullAddress($item['ward_id']);
            $i++;
        }
        return $res;
    }
    
    public function fetchAllToOptions(){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("status = 1");
        $select->order('id asc');
        try {
            $selectString = $sql->buildSqlString($select);
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
            $arrResults[$item['id']] = $item["id"] . " - " . $item['name'];
        }
        return $arrResults;
    }

    public function fetchAllToOptions1(){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("status = 1");
        $select->order('id asc');
        try {
            $selectString = $sql->buildSqlString($select);
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
            $arrResults[$item['id']] = $item["is_group"]; 
        }
        return $arrResults;
    }
    
    public function fetchRow($id){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("id = '" . $id . "'");
        $select->where("status = 1");
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
    }

    public function addTable($companyId){
        $adapter = $this->adapter;
        $sql = "create table `codes_" . strtolower($companyId) . "` (
            `id` varchar(16) NOT NULL,
            `company_id` varchar(16) DEFAULT NULL,
            `serial` varchar(16) NOT NULL,
            `qrcode` varchar(32) NOT NULL,
            `block_id` int(11) DEFAULT NULL,
            `product_id` int(11) DEFAULT NULL,
            `agent_id` int(11) DEFAULT NULL,
            `storehouse_id` int(11) DEFAULT NULL,
            `phone_id` varchar(32) DEFAULT NULL,
            `number_checked` int(11) DEFAULT '0',
            `number_checked_qrcode` int(11) DEFAULT '0',
            `allow_check` int(11) DEFAULT '1',
            `is_exist` tinyint(4) DEFAULT '1',
            `checked_at` datetime DEFAULT NULL,
            `imported_at` datetime DEFAULT NULL,
            `exported_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`,`serial`,`qrcode`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
           $statement = $adapter->query($sql);
           $result = $statement->execute();
           return true;
    }

    public function fetchRowUserId($id){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("user_id = '" . $id . "'");
        //$select->limit(1);
        try {
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        $arr = $results->toArray();
        $res = [];
        $i = 0;
        if(empty($arr)){
            return [];
        }
        foreach($arr as $item){
            $res[$i] = $item['id'];
            $i++;
        }
        return $res;
    }

    public function fetchAllByUserId(){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("status = 1");
        $select->where("user_id = '".\Admin\Service\Authentication::getId()."'");
        $select->order('id asc');
        try {
            $selectString = $sql->buildSqlString($select);
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
            $arrResults[$item['id']] = $item["id"] . " - " . $item['name'];
        }
        return $arrResults;
    }    
}