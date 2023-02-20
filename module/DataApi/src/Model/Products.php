<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace DataApi\Model;

use Exception;
use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Products{
	protected $_name = 'products';
	protected $_primary = array('id');
	
	private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }
    public function fetchAlls($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        // if(\Admin\Service\Authentication::getCompanyId() != ""){
        //     $select->where("company_id = '" . \Admin\Service\Authentication::getCompanyId() . "'");
        // }
        if(isset($options['company_id']) && $options['company_id'] != null && $options['company_id'] != ""){
            $select->where("company_id = '" . $options['company_id'] . "'");
        }
        if(isset($options['keyword'])){
            $select->where("(`name` like '%" . $options['keyword'] . "%' or 
                `code` like '%" . $options['keyword'] . "%' or 
                `barcode` like '%" . $options['keyword'] . "%')");
        }
        // check 'status' exist
        if(isset($options['status'])){
            $select->where("status = '" . $options['status'] . "'");
        }
        $select->where("status != '-1'");
        if (isset($options['page']) && isset($options["limit"])) {
            $select->limit($options["limit"]);
            $select->offset(((int)$options["page"] - 1) * (int)$options["limit"]);
        }
        $select->order('id desc');
        try {
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return [];
        }
        if(empty($results)){
            return [];
        }
        $re = $results->toArray();
        return $re;
    }
    
    /**
     * Get all product as company
     * @param int $companyId
     */
    public function fetchAllAsCompany($companyId = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = '" . $companyId . "'");
        $select->order("id desc");
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return [];
        }
        if(empty($results)){
            return [];
        }
        return $results->toArray();
    }
    
    public function fetchAllOptions01($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        //if(isset($options['company_id']) && $options['company_id'] != null){
            $select->where("company_id = '" . $options["company_id"] . "'");
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
    
    public function fetchAllOptions02($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        // check 'status' exist
        if(isset($options['status'])){
            $select->where("status = '" . $options['status'] . "'");
        }
        $select->order('name asc');
        try {
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
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
            $res[$item['id']] = $item['name'] . ' - Ký hiệu mã: ' . $item['prefix_code'] . ' - Ký hiệu serial: ' . $item['prefix_serial'] . ' - Serial bắt đầu: ' . $item['serial_begin'];
        }
        return $res;
    }
    
    public function fetchAllOptions03($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        //if(isset($options['company_id'])){
            $select->where("company_id = '" . \Admin\Service\Authentication::getCompanyId() . "'");
        //}
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
            $res[$item['id']] = ['code' => $item['code'], 'name' => $item['name']];
        }
        return $res;
    }
    
    public function fetchAllOptionsByBarcodes($str = ""){
        if($str == "") return [];
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = '" . \Admin\Service\Authentication::getCompanyId() . "'");
        $select->where("barcode in (" . $str . ")");
        try {
            $selectString = $sql->buildSqlString($select);
            // echo $selectString; die();
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
            $res[$item['barcode']] = $item["barcode"];
        }
        return $res;
    }
    
    public function fetchRow($id){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("id = '$id'");
        //$select->where("company_id = '" . \Admin\Service\Authentication::getCompanyId() . "'");
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
}