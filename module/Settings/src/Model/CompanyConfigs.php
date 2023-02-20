<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Settings\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class CompanyConfigs{
	protected $_name = 'company_configs';
	
	private $adapter = null;
	
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }
    
    public function fetchAll($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if(isset($options['id'])){
            $select->where("id = '" . $options['id'] . "'");
        }
        if(isset($options['company_id'])){
            $select->where("company_id = '" . $options['company_id'] . "'");
        }
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
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if(isset($options['id'])){
            $select->where("id = '" . $options['id'] . "'");
        }
        if(isset($options['company_id'])){
            $select->where("company_id = '" . $options['company_id'] . "'");
        }
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
        return false;
    }
    
    public function updateRow($id = null, $companyId = null, $data = null){
        if($id == null || $companyId == null || empty($data)){
            return false;
        }
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $update = $sql->update($this->_name);
        $update->set($data);
        $update->where(['id' => $id, 'company_id' => $companyId]);
        try {
            $statement = $sql->prepareStatementForSqlObject($update);
            //$selectString = $sql->buildSqlString($update);
            //echo $selectString; die();
            $results = $statement->execute();
            return $results;
        } catch (Exception $e) {
            return false;
        }
        if(empty($results)){
            return false;
        }
        return true;
    }
}