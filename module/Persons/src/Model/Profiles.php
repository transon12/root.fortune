<?php

namespace Persons\Model;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Profiles{

    protected $_name = 'profiles';
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
        if(\Admin\Service\Authentication::getId() != "1"){
            $select->where("user_id != 1");
        }
        if(isset($options["keyword"])){
            $select->where("(`name` like '%".$options["keyword"]."%')");
        }
        if(isset($options["user_id"])){
            $select->where("user_id not in (".$options["user_id"].")");
        }
        $select->order('sort desc');
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

    public function fetchRow($id){
        $adapter = $this->adapter;
        
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where($this->_name . ".user_id = '$id'");
        if(\Admin\Service\Authentication::getId() != "1"){
            $select->where($this->_name . ".user_id != 1");
        }
        $select->join('users', 'profiles.user_id = users.id', ['avatar', 'firstname', 'lastname', 'password', 'gender'], 'left');
        $select->join('companies', 'users.company_id = companies.id', ['is_group'], 'left');
        
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

    public function fetchRowAsId($id){
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

    public function fetchRowAsUserId($userId){
        $adapter = $this->adapter;
        
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("user_id = '$userId'");
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
            return $results->getGeneratedValue();
        } catch (Exception $e) {
            return [];
        }
        if(empty($results)){
            return false;
        }
        return false;
    }

    public function updateRow($userId, $data){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $update = $sql->update($this->_name);
        $update->set($data);
        $update->where(['user_id' => $userId]);
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

    public function fetchAllByName(){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("user_id != 1");
        $select->where("status = 1");
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return false;
        }
        if(empty($results)){
            return [];
        }
        $re = $results->toArray();
        $arr = [];
        foreach($re as $item){
            $arr[$item['id']] = $item['name'];
        }
        return $arr;
    }

    public function fetchIdByUserId(){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("user_id != 1");
        $select->where("status = 1");
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return false;
        }
        if(empty($results)){
            return [];
        }
        $re = $results->toArray();
        $arr = [];
        foreach($re as $item){
            $arr[$item['user_id']] = $item['id'];
        }
        return $arr;
    }

    public function fetchNameByUserId(){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if(\Admin\Service\Authentication::getId() != 1){
            $select->where("user_id != 1");
        }
        $select->where("status = 1");
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return false;
        }
        if(empty($results)){
            return [];
        }
        $re = $results->toArray();
        $arr = [];
        foreach($re as $item){
            $arr[$item['user_id']] = $item['name'];
        }
        return $arr;
    }

    public function fetchStartDateById(){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("user_id != 1");
        $select->where("status = 1");
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return false;
        }
        if(empty($results)){
            return [];
        }
        $re = $results->toArray();
        $arr = [];
        foreach($re as $item){
            $arr[$item['id']] = $item['start_date'];
        }
        return $arr;
    }
}