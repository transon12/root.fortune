<?php

namespace DataApi\Model;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class CompanyConfigs{
	protected $_name = 'company_configs';
	protected $_primary = array('id');
	
	private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }
    
    /**
     * Lấy tất cả thuộc tính theo danh sách company truyền vào
     */
    public function fetchAllAsCompanyIds($companyIds = "", $id = ""){
        if($companyIds == "" || $id == ""){
            return [];
        }
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("id = '" . $id . "'");
        $select->where("company_id in (" . $companyIds . ")");
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