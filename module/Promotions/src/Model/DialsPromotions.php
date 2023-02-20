<?php
namespace Promotions\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;

class DialsPromotions{
	protected $_name = 'dials_promotions';
	protected $_primary = ['dial_id', 'promotion_id'];
	
	private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
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

    /**
     * Get all data as promotions_id
     */
    public function fetchAllAsDialId($dialId = 0, $toOption = false){
        if($dialId == 0){
            return false;
        }
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("dial_id = '$dialId'");
        $select->order('created_at asc');
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        if(empty($results)){
            return false;
        }
        if(!$toOption){
            return $results->toArray();
        }else{
            $re = [];
            foreach($results->toArray() as $item){
                $re[$item['promotion_id']] = $item['promotion_id']; 
            }
            return $re;
        }
    }
    
    public function deleteRowsAsDialId($dialId = null){
        if($dialId == null) return false;
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $delete = $sql->delete($this->_name);
        $delete->where(['dial_id' => $dialId]);
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
        if(isset($options['promotion_id'])){
            $select->where("promotion_id = '" . $options['promotion_id'] . "'");
        }
        if(isset($options['dial_id'])){
            $select->where("dial_id = '" . $options['dial_id'] . "'");
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
        if(isset($conditions['promotion_id'])){
            $delete->where(['promotion_id' => $conditions['promotion_id']]);
        }
        if(isset($conditions['dial_id'])){
            $delete->where(['dial_id' => $conditions['dial_id']]);
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