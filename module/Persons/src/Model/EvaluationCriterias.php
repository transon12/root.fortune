<?php

namespace Persons\Model;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class EvaluationCriterias{

    protected $_name = 'evaluation_criterias';
    protected $_primary = array('id');

    private $adapter = null;

    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    public function fetchAllCode(){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where('`code` is not null');
        $select->order('id asc');
        try{
            $selectString = $sql->buildSqlString($select);
        //    echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return [];
        }
        if(empty($results)){
            return [];
        }
        $res = $results->toArray();
        $arr = [];
        foreach($res as $item){
            $arr[$item['code']] = ['name'=>$item['name'], 'description'=>$item['description']];
        }
        return $arr;
    }

    public function fetchAllPoint(){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where('`point` is not null');
        $select->order('point desc');
        try{
            $selectString = $sql->buildSqlString($select);
        //    echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return [];
        }
        if(empty($results)){
            return [];
        }
        return $results->toArray();
    }

}