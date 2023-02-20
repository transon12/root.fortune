<?php
namespace Admin\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use Zend\Session\SessionManager;

class Positions{
	protected $_name = 'positions';
	protected $_primary = array('id');
	
	private $adapter = null;
	private $sessionContainer = null;
	
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $manager = new SessionManager();
        $this->sessionContainer = new Container('session_login', $manager);
    }
    
    public function fetchAlls($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if($this->sessionContainer->id != "1"){
            $select->where("id != 1");
        }
        if(isset($options['keyword'])){
            $select->where("name like '%" . $options['keyword'] . "%'");
        }
        if(isset($options['min_level'])){
            $select->where("level > '" . $options['min_level'] . "'");
        }
        $select->where("status = 1");
        
        if(isset($options['company_id']) && $options['company_id'] != null && $options['company_id'] != ""){
            $select->where("company_id = '" . $options['company_id'] . "'");
        }
        
        $select->order('id desc');
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
    
    public function fetchAllOptions($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if($this->sessionContainer->id != "1"){
            $select->where("id != 1");
        }
        if(isset($options['min_level'])){
            $select->where("level > '" . $options['min_level'] . "'");
        }
        if(isset($options['company_id'])){
            $select->where("company_id = '" . $options['company_id'] . "'");
        }
        $select->order('id desc');
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
    
    public function fetchAllOptions1($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if($this->sessionContainer->id != "1"){
            $select->where("id != 1");
        }
        if(isset($options['min_level'])){
            $select->where("level > '" . $options['min_level'] . "'");
        }
        $select->order('id desc');
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
            $res[$item['id']] = $item['id'];
        }
        return $res;
    }

    /**
     * @param array $options['re_options'] ~ return type option (check isset and !empty)
     *     + example fetchAllConditions(['re_options' => ['key' => '[id]-[level]', 'value' => '[name]-[created_at]']]));
     * @param array $options['keyword'] ~ search as keyword
     * @param array $options['order'] ~ search as order, with key is colums in table and value follow: 0 ~ asc, 1 ~ desc. (default id desc)
     *     + example fetchAllConditions(['re_options' => ['key' => '[id]-[level]', 'value' => '[name]-[created_at]'], 'order' => ['key' => 'name', 'value' => 'DESC']])
     */ 
    /*public function fetchAllConditions($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if($this->sessionContainer->id != "1"){
            $select->where("id != 1");
        }
        if(isset($options['keyword'])){
            $select->where("name like '%" . $options['keyword'] . "%'");
        }
        if(isset($options['min_level'])){
            $select->where("level > '" . $options['min_level'] . "'");
        }
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
        // check if re_options
        if(isset($options['re_options'])){
            if(!isset($options['re_options']['key']) || !$options['re_options']['value']){
                $options['re_options'] = [
                    'key' => '[id]',
                    'value' => '[name]'
                ];
            }
            $res = [];
            foreach($re as $item){
                // replace key
                $key = $options['re_options']['key'];
                $key = str_replace('[id]', $item['id'], $key);
                $key = str_replace('[level]', $item['level'], $key);
                // replace value
                $value = $options['re_options']['value'];
                $value = str_replace('[id]', $item['id'], $value);
                $value = str_replace('[name]', $item['name'], $value);
                $value = str_replace('[created_at]', $item['created_at'], $value);
                $res[$key] = $value;
            }
            return $res;
        }
        return $re;
    }*/
    
    public function fetchRow($id){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("id = '$id'");
        if($this->sessionContainer->id != "1"){
            $select->where("id != 1");
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
        return false;
    }
    
    public function updateRow($id, $data){
        if($this->sessionContainer->id != "1"){
            if($id == 1) return false;
        }
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
        return false;
    }
    
    public function deleteRow($id){
        if($id == 1) return false;
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
        return false;
    }
    
    
    /**
     * fetch level min in position
     */
    public function fetchMinMaxLevel($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if(isset($options['ids']) && $options['ids'] != ''){
            $select->where("id in (" . $options['ids'] . ")");
        }else{
            return 1000;
        }
        if(isset($options['order']['key']) && isset($options['order']['value'])){
            $select->order($options['order']['key'] . ' ' . $options['order']['value']);
        }else{
            $select->order('level asc');
        }
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return 1000;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return 1000;
        }
        return $arr[0]['level'];
    }

    public function fetchAllByName(){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where('id != 1');
        $select->order('id desc');
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
}