<?php
namespace Supplies\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;

class SuppliesOuts{
	protected $_name = 'supplies_outs';
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
        if(isset($options['proposal_detail_id'])){
            $select->where("proposal_detail_id = '" . $options['proposal_detail_id'] . "'");
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

    public function fetchAllsGroupStorehouseAsSupplyId($suppliesId = '0'){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->columns(['storehouse_id' => 'storehouse_id', 'total' => new Expression('sum(number)')]);
        if($suppliesId != '0'){
            $select->where("supply_id = '" . $suppliesId . "'");
        }
        $select->group('storehouse_id');
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
            $res[$item['storehouse_id']] = $item['total'];
        }
		return $res;
    }

    public function fetchTotalNumberAsProposalDetail($proposalDetailId = '0'){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->columns(['proposal_detail_id' => 'proposal_detail_id', 'total' => new Expression('sum(number)')]);
        if($proposalDetailId != '0'){
            $select->where("proposal_detail_id = '" . $proposalDetailId . "'");
        }
        $select->group('proposal_detail_id');
        try {
            $selectString = $sql->buildSqlString($select);
            //die($selectString);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return 0;
        }
        
        $re = $results->toArray();
        if(empty($re)){
            return 0;
        }
		return $re[0]['total'];
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
        return false;
    }
}