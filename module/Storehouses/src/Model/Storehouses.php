<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Storehouses\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Settings\Model\Wards;

class Storehouses{
	protected $_name = 'storehouses';
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
        if(isset($options['company_id']) && $options['company_id'] != null){
            $select->where("`company_id` = '" . $options['company_id'] . "'");
        }
        if(isset($options['keyword'])){
            $select->where("`name` like '%" . $options['keyword'] . "%' or 
                `address` like '%" . $options['keyword'] . "%'");
        }
        // check 'status' exist
        if(isset($options['status'])){
            $select->where("status = '" . $options['status'] . "'");
        }
        // check 'order' exist
        if(isset($options['order']['key']) && isset($options['order']['value'])){
            $select->order($options['order']['key'] . ' ' . $options['order']['value']);
        }else{
            $select->order('id desc');
        }
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
        $entityWards = new Wards($adapter);
        $i = 0;
        foreach($re as $item){
            $res[$i] = $item;
            $res[$i]['full_address'] = $item['address'] . $entityWards->fetchFullAddress($item['ward_id']);
            $i++;
        }
        return $res;
    }
    
    public function fetchAllOptions($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if(isset($options['company_id'])){
            $select->where("company_id = '" . $options["company_id"] . "'");
        }
        $select->order('name asc');
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
        foreach($re as $item){
            $res[$item['id']] = $item['name'];
        }
        return $res;
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