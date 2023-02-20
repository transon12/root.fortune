<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Settings\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Settings{
	protected $_name = 'settings';
	protected $_primary = array('code');
	
	private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }
    
    public function fetchAll(){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
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
    
    public function fetchRow($code){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("`code` = '$code'");
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
    
    public function updateRow($code, $data){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $update = $sql->update($this->_name);
        $update->set($data);
        $update->where(['code' => $code]);
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
    
    public function fetchPaginator($companyId = null){
        $adapter = $this->adapter;
        if($companyId != null){
            $entityCompanyConfigs = new CompanyConfigs($adapter);
            $arrConfig = $entityCompanyConfigs->fetchRow(['id' => 'paginators', 'company_id' => $companyId]);
            if(!empty($arrConfig)){
                return json_decode($arrConfig['content'], true);
            }
        }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("`code` = 'paginators'");
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return [
                "per_page" => "9",
                "per_pages" => "9, 19, 49, 99, 199, 499, 999",
                "page_range" => "7"
            ];
        }
        return json_decode($arr[0]['content'], true);
    }
    
    public function fetchMessage($companyId = null){
        $adapter = $this->adapter;
        if($companyId != null){
            $entityCompanyConfigs = new CompanyConfigs($adapter);
            $arrConfig = $entityCompanyConfigs->fetchRow(['id' => 'messages', 'company_id' => $companyId]);
            if(!empty($arrConfig)){
                return json_decode($arrConfig['content'], true);
            }
        }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("`code` = 'messages'");
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return [
                "message_success" => "Chuc mung Quy khach da mua dung san pham chinh hang - mac dinh",
                "message_invalid" => "Quy khach da nhan sai ma so - mac dinh",
                "message_checked" => "Ma so cua Quy khach da duoc kiem tra - mac dinh",
                "message_outdate" => "Ma so cua Quy khach da het han - mac dinh",
                "message_success_agent" => "Chuc mung Dai ly da mua dung san pham chinh hang - mac dinh",
                "message_invalid_agent" => "Dai ly da nhan sai ma so - mac dinh",
                "message_checked_agent" => "Ma so cua Dai ly da duoc kiem tra - mac dinh",
                "message_checked_agent" => "Ma so cua Dai ly da het han - mac dinh"
            ];
        }
        
        
        return json_decode($arr[0]['content'], true);
    }
    
    public function fetchManageProducts($companyId = null){
        $adapter = $this->adapter;
        if($companyId != null){
            $entityCompanyConfigs = new CompanyConfigs($adapter);
            $arrConfig = $entityCompanyConfigs->fetchRow(['id' => 'manage_products', 'company_id' => $companyId]);
            if(!empty($arrConfig)){
                $data = json_decode($arrConfig['content'], true);
                $results = array();
                if(!empty($data)){
                    $n = count($data);
                    
                    for($i = 0; $i < $n; $i++){
                        $min = 1000;
                        $temp = array();
                        $tempKey = "";
                        foreach($data as $key => $item){
                            if($item["status"] <= $min){
                                $min = $item["status"];
                                $tempKey = $key;
                                $temp = $item;
                            }
                        }
                        $results[$tempKey] = $temp;
                        unset($data[$tempKey]);
                    }

                }
                //\Zend\Debug\Debug::dump($data); die();
                return $results;
            }
        }
        return [
            "code" => ["name" => "Mã sản phẩm", "type" => "Text", "status" => 1],
            "name" => ["name" => "Tên sản phẩm", "type" => "Text", "status" => 2],
            "created_at" => ["name" => "Ngày tạo", "type" => "Datetime", "status" => 3],
        ];
    }
    
    public function fetchFormProducts($companyId = null){
        $adapter = $this->adapter;
        if($companyId != null){
            $entityCompanyConfigs = new CompanyConfigs($adapter);
            $arrConfig = $entityCompanyConfigs->fetchRow(['id' => 'form_products', 'company_id' => $companyId]);
            if(!empty($arrConfig)){
                return json_decode($arrConfig['content'], true);
            }
        }
        return [];
    }
    
    public function fetchManageAgents($companyId = null){
        $adapter = $this->adapter;
        if($companyId != null){
            $entityCompanyConfigs = new CompanyConfigs($adapter);
            $arrConfig = $entityCompanyConfigs->fetchRow(['id' => 'manage_agents', 'company_id' => $companyId]);
            if(!empty($arrConfig)){
                return json_decode($arrConfig['content'], true);
            }
        }
        return [
            "code" => ["name" => "Mã đại lý", "type" => "Text", "status" => 1],
            "name" => ["name" => "Tên đại lý", "type" => "Text", "status" => 2],
            "created_at" => ["name" => "Ngày tạo", "type" => "Datetime", "status" => 3],
        ];
    }
    
    public function fetchFormAgents($companyId = null){
        $adapter = $this->adapter;
        if($companyId != null){
            $entityCompanyConfigs = new CompanyConfigs($adapter);
            $arrConfig = $entityCompanyConfigs->fetchRow(['id' => 'form_agents', 'company_id' => $companyId]);
            if(!empty($arrConfig)){
                return json_decode($arrConfig['content'], true);
            }
        }
        return [];
    }
    
    public function fetchConfigCodes($companyId = null){
        $adapter = $this->adapter;
        if($companyId != null){
            $entityCompanyConfigs = new CompanyConfigs($adapter);
            $arrConfig = $entityCompanyConfigs->fetchRow(['id' => 'config_codes', 'company_id' => $companyId]);
            if(!empty($arrConfig)){
                //echo $arrConfig['content']; die();
                return json_decode($arrConfig['content'], true);
            }
        }
        return [];
    }
    
    public function fetchSupplies($companyId = null){
        $adapter = $this->adapter;
        if($companyId != null){
            $entityCompanyConfigs = new CompanyConfigs($adapter);
            $arrConfig = $entityCompanyConfigs->fetchRow(['id' => 'supplies', 'company_id' => $companyId]);
            if(!empty($arrConfig)){
                return json_decode($arrConfig['content'], true);
            }
        }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("`code` = 'supplies'");
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return [
                "time_limit_supplies_ins"       => "3599",
                "time_limit_supplies_outs"      => "59",
                "time_limit_proposal_details"   => "59"
            ];
        }
        return json_decode($arr[0]['content'], true);
    }
    
    public function fetchLayouts($companyId = null){
        $adapter = $this->adapter;
        if($companyId != null){
            $entityCompanyConfigs = new CompanyConfigs($adapter);
            $arrConfig = $entityCompanyConfigs->fetchRow(['id' => 'layouts', 'company_id' => $companyId]);
            if(!empty($arrConfig)){
                //echo $arrConfig['content']; die();
                return json_decode($arrConfig['content'], true);
            }
        }
        return [];
    }
    
    public function fetchDisplays($companyId = null){
        $adapter = $this->adapter;
        if($companyId != null){
            $entityCompanyConfigs = new CompanyConfigs($adapter);
            $arrConfig = $entityCompanyConfigs->fetchRow(['id' => 'displays', 'company_id' => $companyId]);
            if(!empty($arrConfig)){
                //echo $arrConfig['content']; die();
                return json_decode($arrConfig['content'], true);
            }
        }
        return [];
    }
    
    public function fetchFormImports($companyId = null){
        $adapter = $this->adapter;
        if($companyId != null){
            $entityCompanyConfigs = new CompanyConfigs($adapter);
            $arrConfig = $entityCompanyConfigs->fetchRow(['id' => 'form_imports', 'company_id' => $companyId]);
            if(!empty($arrConfig)){
                return json_decode($arrConfig['content'], true);
            }
        }
        return [];
    }
}