<?php

namespace Storehouses\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Settings\Model\Wards;

class Agents{
	protected $_name = 'agents';
	protected $_primary = array('id');
	
	private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    public function fetchAll($id,$options= null){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("id = $id");
        if(isset($options['company_id']) && $options['company_id'] != null){
            $select->where("company_id = '" . $options['company_id'] . "'");
        }
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return [];
        }
        return $results->toArray();
    }
    
    public function fetchAlls($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if(isset($options['company_id']) && $options['company_id'] != null){
            $select->where("company_id = '" . $options['company_id'] . "'");
        }
        if(isset($options['keyword'])){
            $select->where("(`name` like '%" . $options['keyword'] . "%' 
            or `code` like '%" . $options['keyword'] . "%')");
        }
        if(isset($options['user_id']) && $options['user_id'] != 1){
            if(isset($options['view_all_agent']) && (int)$options['view_all_agent'] != 1){
                $select->where("`user_id` = '" . $options['user_id'] . "'");
            }
            if($options['user_id'] != ""){
                $select->where("`user_id` = '" . $options['user_id'] . "'");
            }
        }
        $select->where("status != '-1'");
        $select->order('created_at desc');
        try {
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
           // \Zend\Debug\Debug::dump($results); die();
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
    
    public function fetchAllToOptions($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = '" . $options["company_id"] . "'");
        $select->where("status = 1");
        $select->order('created_at desc');
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
            $arrResults[$item['id']] = $item['name']; 
        }
        return $arrResults;
    }
    public function fetchAllOptions01($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        //if(isset($options['company_id']) && $options['company_id'] != null){
            // $select->where("company_id = '" . $options["company_id"] . "'");
        //}
        $select->where("status != -1");
        $select->order('name asc');
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return [];
        }
        if(empty($results)){
            return [];
        }
        $re = $results->toArray();
        $res = [];
        foreach($re as $item){
            $res[$item['id']] = $item['name'];
        }
        return $res;
    }
	
    public function fetchAllToOptionsByCode($str = ""){
        if($str == "") return [];
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = '" . \Admin\Service\Authentication::getCompanyId() . "'");
        $select->where("code in (" . $str . ")");
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
            $arrResults[$item['code']] = $item['id']; 
        }
        return $arrResults;
    }
    
    public function fetchRow($id){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("id = '$id'");
        if(\Admin\Service\Authentication::getId() != 1){
            $select->where("company_id = '" . \Admin\Service\Authentication::getCompanyId() . "'");
        }
        $select->limit(1);
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return [];
        }
        return $arr[0];
    }
    
    public function fetchRowCode($code){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = '" . \Admin\Service\Authentication::getCompanyId() . "'");
        $select->where("code = '$code'");
        $select->limit(1);
        try {
            $selectString = $sql->buildSqlString($select);
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
    
    public function fetchRowCodes($codes){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = '" . \Admin\Service\Authentication::getCompanyId() . "'");
        $select->where("code in (" . $codes . ")");
        //$select->limit(1);
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
            return $results->getGeneratedValue();
        } catch (Exception $e) {
            return false;
        }
        if(empty($results)){
            return false;
        }
        return false;
    }
    
    public function runSql($sql){
        if($sql == "") return true;
        $adapter = $this->adapter;
        try {
            $statement = $adapter->query($sql);
            $result = $statement->execute();
        } catch (Exception $e) {
            throw new RuntimeException('Error, contact administrator!');
        }
        return true;
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

    public function fetchAllToOption($userId = null){ //$userId = null
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("status = 1");
        $select->where("user_id = $userId");
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
            $arrResults[$item['id']] = $item["code"] . " - " . $item['name']; 
        }
        return $arrResults;
    }
}