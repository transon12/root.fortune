<?php

namespace Persons\Model;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class RewardDisciplines{

    protected $_name = 'reward_disciplines';
    protected $_primary = array('id');

    private $adapter = null;

    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    public function fetchAlls($options = null){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where('status = 1');
        $select->order("created_at desc");
        if(isset($options["type"]) && $options['type'] != ''){
            $select->where("(`type` = '".$options["type"]."')");
        }
        if(isset($options["keyword"])){
            // $select->where("(`profile_id` like '%".$options["keyword"]."%')");
            $entityProfiles = new Profiles($adapter);
            $arrProfiles = $entityProfiles->fetchAlls(['keyword' => $options['keyword']]);
            $arrProfileId =[];
            $i = 0;
            foreach($arrProfiles as $item){
                $arrProfileId[$i] = $item['id'];
                $i++;
            }
            $strProfileId = implode(" or profile_id = ", $arrProfileId);
            if($strProfileId == ""){
                $select->where("(profile_id = '')");
            }else{
                $select->where("(profile_id = $strProfileId)");
            }
        }
        
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

    public function addRow($data){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $insert = $sql->insert($this->_name);
        $insert->values($data);
        try {
            $statement = $sql->prepareStatementForSqlObject($insert);
            // $statement->$sql->buildSqlString($insert);
            // \Zend\Debug\Debug::dump($statement) ; die();
            $results = $statement->execute();
            return $results;
        } catch (Exception $e) {
            return [];
        }
        if(empty($results)){
            return false;
        }
        return false;
    }

    public function fetchRow($id){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("id = '".$id."'");
        $select->limit(1);
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return [];
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return false;
        }
        return $arr[0];
    }

    public function updateRow($data, $id){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $update = $sql->update($this->_name);
        $update->set($data);
        $update->where("id = '".$id."'");
        try{
            $statement = $sql->prepareStatementForSqlObject($update);
            $results = $statement->execute();
        }catch(Exception $e){
            return false;
        }
        if(empty($results)){
            return false;
        }
        return false;
    }
}