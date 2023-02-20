<?php
namespace Promotions\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;

class ListDials{
	protected $_name = 'list_dials';
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
        // check company id
        if(isset($options['company_id']) && $options['company_id'] != null){
            $select->where("company_id = '" . $options['company_id'] . "'");
        }
        // check 'keyword' exist
        if(isset($options['keyword'])){
            $select->where("(`code_id` like '%" . $options['keyword'] . "%' or 
                `phone_id` like '%" . $options['keyword'] . "%')");
        }
        if(isset($options['datetime_begin'])){
            if($options['datetime_begin'] != ''){
                $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $options['datetime_begin']);
                $select->where("`created_at` >= '" . date_format($datetimeBegin, 'Y-m-d H:i:s') . "'");
            }
        }
        if(isset($options['datetime_end'])){
            if($options['datetime_end'] != ''){
                $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $options['datetime_end']);
                $select->where("`created_at` <= '" . date_format($datetimeEnd, 'Y-m-d H:i:s') . "'");
            }
        }
        if(isset($options['promotion_id'])){
            if($options['promotion_id'] != null){
                $select->where("promotion_id = '" . $options['promotion_id'] . "'");
            }
        }
        if(isset($options['dial_id'])){
            if($options['dial_id'] != ''){
                $entityDialsPromotions = new DialsPromotions($adapter);
                $arrPromotionsId = $entityDialsPromotions->fetchAllAsDialId($options['dial_id'], true);
                if(!empty($arrPromotionsId)){
                    $strDialsPromotions = "'" . implode("','", $arrPromotionsId) . "'";
                    $select->where("promotion_id in (" . $strDialsPromotions . ")");
                }
            }
        }
        $select->order('id desc');
        try {
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
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

    /**
     * @param $options['re_options'] ~ return type option (check isset and !empty)
     *     + example fetchAllConditions(['re_options' => ['key' => '[id]-[level]', 'value' => '[name]-[created_at]']]));
     * @param $options['keyword'] ~ search as keyword
     * @param $options['order'] ~ search as order, with key is colums in table and value follow: 0 ~ asc, 1 ~ desc. (default id desc)
     *     + example fetchAllConditions(['re_options' => ['key' => '[id]-[level]', 'value' => '[name]-[created_at]'], 'order' => ['key' => 'name', 'value' => 'DESC']])
     */ 
    /*public function fetchAllConditions($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        // check 'keyword' exist
        if(isset($options['keyword'])){
            $select->where("`codes_id` like '%" . $options['keyword'] . "%' or 
                `phones_id` like '%" . $options['keyword'] . "%'");
        }
        if(isset($options['datetime_begin'])){
            if($options['datetime_begin'] != ''){
                $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $options['datetime_begin']);
                $select->where("`created_at` >= '" . date_format($datetimeBegin, 'Y-m-d H:i:s') . "'");
            }
        }
        if(isset($options['datetime_end'])){
            if($options['datetime_end'] != ''){
                $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $options['datetime_end']);
                $select->where("`created_at` <= '" . date_format($datetimeEnd, 'Y-m-d H:i:s') . "'");
            }
        }
        if(isset($options['promotion_id'])){
            if($options['promotion_id'] != null){
                $select->where("promotions_id = '" . $options['promotion_id'] . "'");
            }
        }
        if(isset($options['dials_id'])){
            if($options['dials_id'] != ''){
                $entityDialsPromotions = new DialsPromotions($adapter);
                $arrPromotionsId = $entityDialsPromotions->fetchAllAsDialId($options['dials_id'], true);
                if(!empty($arrPromotionsId)){
                    $strDialsPromotions = "'" . implode("','", $arrPromotionsId) . "'";
                    $select->where("promotions_id in (" . $strDialsPromotions . ")");
                }
            }
        }
        // check 'order' exist
        if(isset($options['order']['key']) && isset($options['order']['value'])){
            $select->order($options['order']['key'] . ' ' . $options['order']['value']);
        }else{
            $select->order('id desc');
        }
        try {
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return [];
        }
        if(empty($results)){
            return [];
        }
        $re = $results->toArray();
        // check if re_options
        if(isset($options['re_options'])){
            if(!isset($options['re_options']['key']) || !$options['re_options']['value']){
                $options['re_options'] = [
                    'key' => '[id]',
                    'value' => '[name]'
                ];
            }
            $res = [];
            foreach($re as $item){
                // replace key
                $key = $options['re_options']['key'];
                $key = str_replace('[id]', $item['id'], $key);
                // replace value
                $value = $options['re_options']['value'];
                $value = str_replace('[name]', $item['name'], $value);
                $res[$key] = $value;
            }
            return $res;
        }
        return $re;
    }*/
    
    public function fetchCount($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->columns(['total' => new Expression('count(*)')]);
        if(isset($options['promotion_id'])){
            $select->where("promotions_id = '" . $options['promotion_id'] . "'");
        }
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return 0;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return 0;
        }
        return $arr[0]['total'];
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
    
    public function addRow($data){
        $adapter = $this->adapter;
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

    /**
     * 
     * @param unknown $strPromotionsId: list promotions_id
     * @param number $number: limit list
     * @param unknown $datetimeBegin
     * @param unknown $datetimeEnd
     */
    public function fetchRowWinner($strPromotionsId = null, $number = 1, $datetimeBegin = null, $datetimeEnd = null, $winMore = 1, $strPrizes = ''){
        if($strPromotionsId == null) return false;
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = '" . COMPANY_ID . "'");
        if($strPromotionsId != ''){
            $select->where("promotion_id in (" . $strPromotionsId . ")");
        }
        if($datetimeBegin != null){
            $select->where("created_at >= '" . $datetimeBegin . "'");
        }
        if($datetimeEnd != null){
            $select->where("created_at <= '" . $datetimeEnd . "'");
        }
        if($winMore == 0){
            $entityWinnerDials = new WinnerDials($adapter);
            $strPhone = $entityWinnerDials->fetchWinnerWon($strPrizes);
            if($strPhone != ''){
                $select->where("phone_id not in (" . $strPhone . ")");
            }
        }
        // get limit
        $select->limit($number);
        // random
        $select->order(new Expression('rand()'));
        try {
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return false;
        }
        return $arr;
    }
    
    public function deleteRows($conditions = []){
        if(empty($conditions)) return false;
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $delete = $sql->delete($this->_name);
        if(isset($conditions['promotion_id'])){
            $delete->where(['promotions_id' => $conditions['promotion_id']]);
        }
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