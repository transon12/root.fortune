<?php

namespace Codes\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;

class CodeRootsQrcode{
	protected $_name = 'code_roots_qrcode';
	protected $_primary = array('random', 'id');
	
	private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }
    
    public function fetchAllLimit($number){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->limit($number);
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
    
    public function countAll(){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->columns(['total' => new Expression('count(*)')]);
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return 0;
        }
        return $arr[0]['total'];
    }
    
    /**
     * Delete rows after add to table codes
     */
    public function deleteRows($ids){
        $adapter = $this->adapter;
        $sql = 'delete from code_roots_qrcode where id in (' . $ids . ')';
        try {
            $statement = $adapter->query($sql);
            $result = $statement->execute();
        } catch (Exception $e) {
            return false;
        }
        if(empty($results)){
            return false;
        }
        return true;
    }
    
    /**
     * Delete rows after add to table codes
     */
    public function addRows($sql){
        if($sql == '') return true;
        $adapter = $this->adapter;
        try {
            $statement = $adapter->query($sql);
            $result = $statement->execute();
        } catch (Exception $e) {
            return false;
        }
        if(empty($results)){
            return false;
        }
        return true;
    }
}