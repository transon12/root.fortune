<?php
namespace Admin\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;

class GroupsPositionsUsers{
	protected $_name = 'groups_positions_users';
	protected $_primary = array('id');
	
	private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }
    
    public function fetchAll(){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("status != -1");
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
        return $results->toArray();
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
    
    public function updateRows($options = null, $data = null){
        if($data == null || (!isset($options['group_id']) && 
            !isset($options['position_id']) && 
            !isset($options['user_id']))){
            return false;
        }
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $update = $sql->update($this->_name);
        $update->set($data);
        $condition = [];
        if(isset($options['group_id'])){
            $condition['group_id'] = $options['group_id'];
        }
        if(isset($options['position_id'])){
            $condition['position_id'] = $options['position_id'];
        }
        if(isset($options['user_id'])){
            $condition['user_id'] = $options['user_id'];
        }
        $update->where($condition);
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
    
    public function deleteRows($options = []){
        if(!isset($options['group_id']) && 
            !isset($options['position_id']) && 
            !isset($options['user_id'])){
            return false;
        }
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        
        $delete = $sql->delete($this->_name);
        if(isset($options['group_id'])){
            $delete->where(['group_id' => $options['group_id']]);
        }
        if(isset($options['position_id'])){
            $delete->where(['position_id' => $options['position_id']]);
        }
        if(isset($options['user_id'])){
            $delete->where(['user_id' => $options['user_id']]);
        }
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

    /**
     * Get all data with condition users_id
     */
    public function fetchAllAsUsersId($options = null){
        if(!isset($options['user_id'])){
            return false;
        }
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("user_id = '" . $options['user_id'] . "'");
        $select->where("status != -1");
        $select->order('created_at asc');
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
        return $results->toArray();
    }
    
    /**
     * Get all data with condition positions_id
     */
    public function fetchAllAsPositionsId($positions_id = 0){
        if($positions_id == 0){
            return false;
        }
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("position_id = '$positions_id'");
        $select->where("status != -1");
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        if(empty($results)){
            return false;
        }
        return $results->toArray();
    }
    
    /**
     * Get all data with condition groups_id
     */
    public function fetchAllAsGroupsId($groups_id = 0){
        if($groups_id == 0){
            return false;
        }
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("group_id = '$groups_id'");
        $select->where("status != -1");
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
        return $results->toArray();
    }
    
    /**
     * Check user existed in groups
     */
    public function checkUsersIdInGroups($groupsId = 0, $usersId = 0){
        if($usersId == 0 && $groupsId == 0){
            return false;
        }
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("user_id = '$usersId'");
        $select->where("group_id = '$groupsId'");
        $select->where("status != -1");
        $select->order('created_at asc');
        $select->limit(1);
        try {
            $selectString = $sql->buildSqlString($select);
           // echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        if(empty($results)){
            return false;
        }

        $arr = $results->toArray();
        if(empty($arr)){
            return false;
        }
        return $arr[0];
    }
    
    public function fetchCount($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->columns(['total' => new Expression('count(*)')]);
        if(isset($options['position_id'])){
            $select->where("position_id = '" . $options['position_id'] . "'");
        }
        if(isset($options['user_id'])){
            $select->where("user_id = '" . $options['user_id'] . "'");
        }
        $select->where("status != -1");
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return 0;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return 0;
        }
        return $arr[0]['total'];
    }

    public function fetchAllUserIdByGroupsId($groups_id = 0){
        if($groups_id == 0){
            return false;
        }
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("group_id = '$groups_id'");
        $select->where("status != -1");
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
        $arr = [];
        $res = $results->toArray();
        $i = 0;
        foreach($res as $item){
            $arr[$i] = $item["user_id"];
            $i++;
        }
        return $arr;
    }
}