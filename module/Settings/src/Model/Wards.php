<?php
namespace Settings\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;
use Settings\Model\Countries;
use Settings\Model\Cities;
use Settings\Model\Districts;

class Wards{
	protected $_name = 'wards';
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
        if(isset($options['keyword'])){
            $select->where("`id` like '%" . $options['keyword'] . "%' or 
                `name` like '%" . $options['keyword'] . "%'");
        }
        $select->where("status = 1");
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
    
    public function fetchAllOptions($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if(isset($options['keyword'])){
            $select->where("`id` like '%" . $options['keyword'] . "%' or 
                `name` like '%" . $options['keyword'] . "%'");
        }
        if(isset($options['district_id'])){
            $select->where("`district_id` = '" . $options['district_id'] . "'");
        }
        $select->where("status = 1");
        $select->order('name asc');
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        if(empty($results)){
            return false;
        }
        $re = $results->toArray();
        
        $res = [];
        foreach($re as $item){
            $res[$item['id']] = $item['name'];
        }
        return $res;
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
    
    public function fetchFullAddress($id){
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
        $ward = $arr[0];
        $entityDistricts = new Districts($adapter);
        if(isset($ward['district_id']) && $ward['district_id'] != '' && $ward['district_id'] != null){
            $district = $entityDistricts->fetchRow($ward['district_id']);
        }
        $entityCities = new Cities($adapter);
        if(isset($district['city_id']) && $district['city_id'] != '' && $district['city_id'] != null){
            $city = $entityCities->fetchRow($district['city_id']);
        }
        $entityCountries = new Countries($adapter);
        if(isset($city['country_id']) && $city['country_id'] != '' && $city['country_id'] != null){
            $country = $entityCountries->fetchRow($city['country_id']);
        }
        $address = isset($ward['name']) ? ', ' . $ward['name'] : '';
        $address = $address . (isset($district['name']) ? ', ' . $district['name'] : '');
        $address = $address . (isset($city['name']) ? ', ' . $city['name'] : '');
        $address = $address . (isset($country['name']) ? ', ' . $country['name'] : '');
        return $address;
    }
    
    public function fetchFullId($id){
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
        $ward = $arr[0];
        $entityDistricts = new Districts($adapter);
        if(isset($ward['district_id']) && $ward['district_id'] != '' && $ward['district_id'] != null){
            $district = $entityDistricts->fetchRow($ward['district_id']);
        }
        $entityCities = new Cities($adapter);
        if(isset($district['city_id']) && $district['city_id'] != '' && $district['city_id'] != null){
            $city = $entityCities->fetchRow($district['city_id']);
        }
        $entityCountries = new Countries($adapter);
        if(isset($city['country_id']) && $city['country_id'] != '' && $city['country_id'] != null){
            $country = $entityCountries->fetchRow($city['country_id']);
        }
        $address['ward_id'] = isset($ward['id']) ? ', ' . $ward['id'] : '';
        $address['district_id'] = isset($district['id']) ? $district['id'] : '';
        $address['city_id'] = isset($city['id']) ? $city['id'] : '';
        $address['country_id'] = isset($country['id']) ? $country['id'] : '';
        return $address;
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
        if(empty($results)){
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
        if(empty($results)){
            return false;
        }
        return false;
    }
}