<?php

namespace DataApi\Model;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Companies{
	protected $_name = 'companies';
	protected $_primary = array('id');
	
	private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    public function fetchAllAsUserId($userId = 0, $options = []){
        if($userId == 0){
            return [];
        }
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("user_id = '" . $userId . "'");
        if(isset($options['keyword'])){
            $select->where("(`name` like '%" . $options['keyword'] . "%' 
            or `id` like '%" . $options['keyword'] . "%')");
        }
        if (isset($options['page']) && isset($options["limit"])) {
            $select->limit($options["limit"]);
            $select->offset(((int)$options["page"] - 1) * (int)$options["limit"]);
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
    
}