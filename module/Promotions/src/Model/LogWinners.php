<?php
namespace Promotions\Model;

use Exception;
use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class LogWinners{
	protected $_name = 'log_winners';
	protected $_primary = array('id');
	
	private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }
    
    public function fetchAlls($options = null){
        if(!isset($options["winner_promotion_id"])){
            return [];
        }
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        // $select->where("user_id = '" . $options['user_id'] . "'");
        $select->where("winner_promotion_id = '" . $options['winner_promotion_id'] . "'");
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
        return $re;
    }
    
    public function addRow($data){
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
}