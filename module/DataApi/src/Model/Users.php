<?php

namespace DataApi\Model;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Users{
    protected $_name = 'users';
    protected $_primary = array('id');

    private $adapter = null;

    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    public function fetchAlls($idNotIn = ""){
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);

        if(isset($options['company_id']) && $options['company_id'] != null && $options['company_id'] != ""){
            $select->where("company_id = '" . $options['company_id'] . "'");
        }
        
        $select->where("`status` = '1'");
        $select->where("`company_id` = ''");
        if($idNotIn != ""){
            $select->where("`id` not in (" . $idNotIn . ")");
        }
        $select->order('firstname asc');
        try{
            $selectString = $sql->buildSqlString($select);
            // echo $selectString; die();
            $results = $this->adapter->query($selectString, $this->adapter::QUERY_MODE_EXECUTE);
        }catch (Exception $e){
            return [];
        }
        if(empty($results)){
            return [];
        }
        return $results->toArray();
    }
}