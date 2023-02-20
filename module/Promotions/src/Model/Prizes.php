<?php
namespace Promotions\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;

class Prizes{
	protected $_name = 'prizes';
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
        // check 'keyword' exist
        if(isset($options['keyword'])){
            $select->where("name like '%" . $options['keyword'] . "%'");
        }
        if(isset($options['dial_id'])){
            $select->where("dial_id = '" . $options['dial_id'] . "'");
        }
        if(isset($options['is_remain'])){
            $select->where("number_win > 0");
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
    
    public function fetchAllOptions01($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if(isset($options['dial_id'])){
            $select->where("dials_id = '" . $options['dial_id'] . "'");
        }
        if(isset($options['is_remain'])){
            $select->where("number_win > 0");
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
        $select->where("company_id = '".COMPANY_ID."' ");
        if(isset($options['dial_id'])){
            $select->where("dial_id = '" . $options['dial_id'] . "'");
        }
        if(isset($options['is_remain'])){
            $select->where("number_win > 0");
        }
        if(isset($options['dial_id'])){
            $select->where("dial_id = '".$options['dial_id']."'");
        }
        $select->order('id desc');
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
            $res[$item['id']] = $item['id'];
        }
        return $res;
    }
    
    public function fetchAllOptions03($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if(isset($options['company_id'])){
            $select->where("company_id = '" . $options['company_id'] . "'");
        }
        if(isset($options['dial_id'])){
            $select->where("dial_id = '" . $options['dial_id'] . "'");
        }
        if(isset($options['is_remain'])){
            $select->where("number_win > 0");
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
        $res = [];
        foreach($re as $item){
            $res[$item['id']] = $item;
        }
        return $res;
    }

    /**
     * @param $options['re_options'] ~ return type option (check isset and !empty)
     *     + example fetchAllConditions(['re_options' => ['key' => '[id]-[level]', 'value' => '[name]-[created_at]']]));
     * @param $options['keyword'] ~ search as keyword
     * @param $options['order'] ~ search as order, with key is colums in table and value follow: 0 ~ asc, 1 ~ desc. (default id desc)
     *     + example fetchAllConditions(['re_options' => ['key' => '[id]-[level]', 'value' => '[name]-[created_at]'], 'order' => ['key' => 'name', 'value' => 'DESC']])
     */ 
    /*public function fetchAllConditions($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        // check 'keyword' exist
        if(isset($options['keyword'])){
            $select->where("name like '%" . $options['keyword'] . "%'");
        }
        if(isset($options['dial_id'])){
            $select->where("dials_id = '" . $options['dial_id'] . "'");
        }
        if(isset($options['is_remain'])){
            $select->where("number_win > 0");
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
                // replace value
                $value = $options['re_options']['value'];
                $value = str_replace('[id]', $item['id'], $value);
                $value = str_replace('[name]', $item['name'], $value);
                $res[$key] = $value;
            }
            return $res;
        }elseif(isset($options['key_is_id'])){
            $res = [];
            foreach($re as $item){
                $res[$item['id']] = $item;
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
    
    public function fetchCount($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->columns(['total' => new Expression('count(*)')]);
        if(isset($options['dial_id'])){
            $select->where("dials_id = '" . $options['dial_id'] . "'");
        }
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
    
    public function deleteRows($conditions = []){
        if(empty($conditions)) return false;
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $delete = $sql->delete($this->_name);
        if(isset($conditions['dial_id'])){
            $delete->where(['dials_id' => $conditions['dial_id']]);
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
}