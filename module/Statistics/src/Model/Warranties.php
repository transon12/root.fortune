<?php
namespace Statistics\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;

class Warranties{
	protected $_name = 'warranties';
	protected $_primary = array('id');
	
	private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
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
        $select->where("id != 1");
        if(isset($options['keyword'])){
            $select->where("title like '%" . $options['keyword'] . "%' or 
                content like '%" . $options['keyword'] . "%'");
        }
        if(isset($options['order']['key']) && isset($options['order']['value'])){
            $select->order($options['order']['key'] . ' ' . $options['order']['value']);
        }else{
            $select->order('id desc');
        }
        if(isset($options['code_id'])){
            $select->where("codes_id = '" . $options['code_id'] . "'");
        }else{
            return [];
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
                    'value' => '[username]'
                ];
            }
            $res = [];
            foreach($re as $item){
                // replace key
                $key = $options['re_options']['key'];
                $key = str_replace('[id]', $item['id'], $key);
                // replace value
                $value = $options['re_options']['value'];
                $value = str_replace('[firstname]', $item['firstname'], $value);
                $value = str_replace('[lastname]', $item['lastname'], $value);
                $res[$key] = $value;
            }
            return $res;
        }
        return $re;
    }*/
    
    public function fetchAlls($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        
        if(isset($options['keyword'])){
            $select->where("`title` like '%" . $options['keyword'] . "%' or 
                `content` like '%" . $options['keyword'] . "%' or 
                `price` like '%" . $options['keyword'] . "%'");
        }
        
        if(isset($options['code_id'])){
            $select->where("code_id = '" . $options['code_id'] . "'");
        }else{
            return [];
        }
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
    
    public function fetchRow($options = null){
        if(!isset($options['id']) || !isset($options['code_id'])) return [];
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("id = '" . $options['id'] . "'");
        $select->where("code_id = '" . $options['code_id'] . "'");
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
    
    public function deleteRow($options){
        if(!isset($options['id']) || !isset($options['code_id'])) return [];
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $delete = $sql->delete($this->_name);
        $delete->where(['id' => $options['id']]);
        $delete->where(['code_id' => $options['code_id']]);
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
    
    public function updateRows($options = null, $data){
        if($options == null) return false;
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $update = $sql->update($this->_name);
        $update->set($data);
        if(isset($options['agent_id'])){
            $update->where(['agents_id' => $options['agent_id']]);
        }
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
}