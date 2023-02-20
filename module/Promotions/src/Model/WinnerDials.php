<?php
namespace Promotions\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;

class WinnerDials{
	protected $_name = 'winner_dials';
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
        
        $select->where($this->_name.".company_id = '" . COMPANY_ID . "'");
        if(isset($options['strPrizes']) && $options['strPrizes'] != ''){
            $select->where("prize_id in (" . $options['strPrizes'] . ")");
        }
        $select->join('prizes', $this->_name . '.prize_id = prizes.id', ['name']);
        if(isset($options['dial_id'])){
            if($options['dial_id'] != ''){
                $select->where("prizes.dial_id = '" . $options['dial_id'] . "'");
            }
        }
        //$select->order($this->_name . '.id desc');
        try {
            $selectString = $sql->buildSqlString($select);
        //    echo $selectString; die("abcc");
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
        if(isset($options['strPrizes'])){
            $select->where("prizes_id in (" . $options['strPrizes'] . ")");
        }
        $select->join('prizes', $this->_name . '.prizes_id = prizes.id', ['name']);
        if(isset($options['dials_id'])){
            if($options['dials_id'] != ''){
                $select->where("prizes.dials_id = '" . $options['dials_id'] . "'");
            }
        }
        // check 'order' exist
        if(isset($options['order']['key']) && isset($options['order']['value'])){
            $select->order($options['order']['key'] . ' ' . $options['order']['value']);
        }else{
            $select->order($this->_name . '.id desc');
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
                $value = str_replace('[name]', $item['name'], $value);
                $res[$key] = $value;
            }
            return $res;
        }
        return $re;
    }*/
    
    /**
     * Get list winner is won
     */
    public function fetchWinnerWon($strPrizes = ''){
        if($strPrizes == '') return false;
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = '" . COMPANY_ID . "'");
        $select->where("prize_id in (" . $strPrizes . ")");
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
        $strPhone = '';
        foreach($arr as $item){
            if($strPhone == ''){
                $strPhone = "'" . $item['phone_id'] . "'";
            }else{
                $strPhone .= ", '" . $item['phone_id'] . "'";
            }
        }
        return $strPhone;
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
    
    public function fetchCount($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->columns(['total' => new Expression('count(*)')]);
        if(isset($options['prize_id'])){
            $select->where("prizes_id = '" . $options['prize_id'] . "'");
        }
        if(isset($options['list_dial_id'])){
            $select->where("list_dials_id = '" . $options['list_dial_id'] . "'");
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
    
    public function deleteRows($conditions = []){
        if(empty($conditions)) return false;
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $delete = $sql->delete($this->_name);
        if(isset($conditions['prize_id'])){
            $delete->where(['prizes_id' => $conditions['prize_id']]);
        }
        if(isset($conditions['list_dial_id'])){
            $delete->where(['list_dials_id' => $conditions['list_dial_id']]);
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
}