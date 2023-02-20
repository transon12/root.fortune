<?php

namespace Companies\Model;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Missions{
    protected $_name = 'missions';

    private $adapter = null;

    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    public function fetchAll(){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->order('created_at desc');
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return [];
        }
        $arr = [];
        $re = $results->toArray();
        foreach($re as $item){
            $arr[$item['id']] = $item['name'];
        }
        return $arr;
    }
}