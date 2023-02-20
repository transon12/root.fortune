<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Storehouses\Model;

use Exception;
use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class BillDetails{
    protected $_name = 'bill_details';
    protected $_primary = array('id');

    private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    public function fetchAll($id){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("bill_id= $id");
        $select->order('id DESC');
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
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
    public function deleteRow($string){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $delete = $sql->delete($this->_name);
        $delete->where(['code_serial' => $string]);
        try {
            $statement = $sql->prepareStatementForSqlObject($delete);
            //\Zend\Debug\Debug::dump($statement); die();
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