<?php
namespace DataApi\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;

class Codes{
	protected $_name = 'codes';
	protected $_primary = array('id');
	
	private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }
    public function fetchAlls($companyId = null, $options = null){
        if((!isset($options['keyword']) || !isset($options['condition'])) && !isset($options['is_all'])) return [];
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name . "_" . strtolower($companyId));

        if(isset($options['condition'])){
            if($options['condition'] == 2){
                $arrQrcode = explode("=", $options['keyword']);
                $qrcode = $arrQrcode[0];
                if(isset($arrQrcode[1])){
                    $qrcode = $arrQrcode[1];
                }
                $select->where("`qrcode` = '" . $qrcode . "'");
            }elseif($options['condition'] == 1){
                $select->where("`serial` = '" . $options['keyword'] . "'");
            }else{
                $select->where("`id` = '" . $options['keyword'] . "'");
            }
        }
        
        if(isset($options['imports'])){
                $select->where("storehouse_id is not null");
            // if($options['status'] == "1"){
            // }else{
            //     $select->where("storehouse_id is null");
            // }
        }

        if(isset($options['exports'])){
            //$select->where("agent_id is not null");
            if($options['status'] == "1"){
                $select->where("agent_id is not null");
            }else{
                $select->where("agent_id is null");
            }
        }

        if(isset($options['export'])){
            // $select->where("agent_id is not null");
            $select->where("storehouse_id is null");
        }

        if(isset($options['import'])){
            $select->where("agent_id is null");
        }

        if(isset($options['is_all'])){
            if(isset($options['is_serial'])){
                if($options['is_serial'] == '1'){
                    $select->where("qrcode = '" . $options['value_begin'] . "'");
                }elseif($options['is_serial'] == '2'){
                    $select->where("serial = '" . $options['value_begin'] . "'");
                }elseif($options['is_serial'] == '3'){
                    $select->where("serial >= '" . $options['value_begin'] . "'");
                    $select->where("serial <= '" . $options['value_end'] . "'");
                }elseif($options['is_serial'] == '0'){
                    $select->where("id = '" . $options['value_begin'] . "'");
                }else{
                    return [];
                }
            }else{
                return [];
            }
        }
        // check 'order' exist
        if(isset($options['order']['key']) && isset($options['order']['value'])){
            $select->order($options['order']['key'] . ' ' . $options['order']['value']);
        }/*else{
            $select->order('id asc');
        }*/
        try {
            $selectString = $sql->buildSqlString($select);
            //\Zend\Debug\Debug::dump($selectString); die();
            // echo $selectString; die();
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
    
    public function fetchAllToOptionsAsQrcodes($str){
        if($str == "") return [];
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name . "_" . strtolower(\Admin\Service\Authentication::getCompanyId()));
        $select->where("company_id = '" . \Admin\Service\Authentication::getCompanyId() . "'");
        $select->where("qrcode in (" . $str . ")");
        try {
            $selectString = $sql->buildSqlString($select);
            // echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        if(empty($results)){
            return false;
        }
        $arrResults = [];
        $re = $results->toArray();
        foreach($re as $item){
            $arrResults[$item['qrcode']] = [
                "imported_at"   => $item["imported_at"],
                "exported_at"   => $item["exported_at"]
            ]; 
        }
        return $arrResults;
    }
    
    public function fetchAllToOptionsAsSerials($str){
        if($str == "") return [];
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name . "_" . strtolower(\Admin\Service\Authentication::getCompanyId()));
        $select->where("company_id = '" . \Admin\Service\Authentication::getCompanyId() . "'");
        $select->where("`serial` in (" . $str . ")");
        try {
            $selectString = $sql->buildSqlString($select);
            // echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        if(empty($results)){
            return false;
        }
        $arrResults = [];
        $re = $results->toArray();
        foreach($re as $item){
            $arrResults[$item['serial']] = [
                "imported_at"   => $item["imported_at"],
                "exported_at"   => $item["exported_at"]
            ]; 
        }
        return $arrResults;
    }
    
    public function fetchCountByDate($companyId = null, $options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name . "_" . strtolower($companyId));
        $col1 = ($options['type'] == "imported_at") ? ['storehouse_id'] : ['agent_id'];
        $col2 = ($options['type'] == "imported_at") ? ['total_inventory' => new Expression('sum(if(exported_at IS NULL, 1, 0))')] : [];
        $select->columns(array_merge([
            'date_at' => new Expression('DATE_FORMAT(' . $options['type'] . ', "%d/%m/%Y")'), 
            'product_id', 
            // 'storehouse_id', 
            'total' => new Expression('count(*)'), 
            // 'total_inventory' => new Expression('sum(if(exported_at IS NULL, 1, 0))')
        ], $col1, $col2));
        $select->where($options['type'] . " is not null");
        if(isset($options['agent_id'])){
            $select->where("agent_id = '" . $options['agent_id'] . "'");
        }
        if(isset($options['storehouse_id'])){
            $select->where("storehouse_id = '" . $options['storehouse_id'] . "'");
        }
        if(isset($options['product_id'])){
            if($options['product_id'] != "0"){
                $select->where("product_id = '" . $options['product_id'] . "'");
            }
        }
        if(isset($options['datetime_begin'])){
            if($options['datetime_begin'] != ''){
                $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $options['datetime_begin']);
                $select->where("`exported_at` >= '" . date_format($datetimeBegin, 'Y-m-d H:i:s') . "'");
            }
        }
        if(isset($options['datetime_end'])){
            if($options['datetime_end'] != ''){
                $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $options['datetime_end']);
                $select->where("`exported_at` <= '" . date_format($datetimeEnd, 'Y-m-d H:i:s') . "'");
            }
        }

        $select->group('product_id');
        $select->group(($options['type'] == "imported_at") ? 'storehouse_id' : 'agent_id');
        $select->group('date_at');
        
        if (isset($options['page']) && isset($options["limit"])) {
            $select->limit($options["limit"]);
            $select->offset(((int)$options["page"] - 1) * (int)$options["limit"]);
        }
        $select->order('date_at desc');
        try{
            $selectString = $sql->buildSqlString($select);
            // return $selectString;
            // echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return 0;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return [];
        }
        return $arr;
    }
    
    public function fetchDetailByDate($companyId = null, $options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name . "_" . strtolower($companyId));
        $select->columns([
            'date_at' => new Expression('DATE_FORMAT(' . $options['type'] . ', "%d/%m/%Y")'),
            'serial',
            'qrcode',
            'product_id',
            'agent_id',
            'storehouse_id',
            'imported_at',
            'exported_at'
        ]);
        $select->where($options['type'] . " is not null");
        if(isset($options['agent_id'])){
            $select->where("agent_id = '" . $options['agent_id'] . "'");
        }
        if(isset($options['product_id'])){
            $select->where("product_id = '" . $options['product_id'] . "'");
        }

        //$select->group('product_id');
        //$select->group('date_at');
        $select->having("date_at = '" . $options['date_at'] . "'");
        
        $select->order('serial desc');
        try{
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return 0;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return [];
        }
        return $arr;
    }
    
    /**
     * Count codes is not export in date
     * @param unknown $options
     * @return number|unknown
     */
    public function fetchCountByDateNotExport($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name . "_" . strtolower($options['company_id']));
        $select->columns(['date_at' => new Expression('DATE_FORMAT(imported_at, "%d/%m/%Y")'), 'product_id', 'total' => new Expression('count(*)')]);
        
        $select->where("storehouse_id = '" . $options['storehouse_id'] . "'");
        
        $select->where("imported_at is not null");
        $select->where("exported_at is null");
        
        $select->group('date_at');
        $select->group('product_id');
        
        $select->order('date_at desc');
        try{
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return 0;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return [];
        }
        $ar = array();
        foreach($arr as $item){
            $ar[$item['date_at'] . "-" . $item['product_id']] = $item['total'];
        }
        return $ar;
    }
    
    /**
     * 
     */
    public function fetchDetailByDateNotExport($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name . "_" . strtolower($options['company_id']));
        $select->columns(['date_at' => new Expression('DATE_FORMAT(imported_at, "%d/%m/%Y")'), 'product_id', 'serial', 'qrcode', 'imported_at']);
        
        $select->where("storehouse_id = '" . $options['storehouse_id'] . "'");
        if(isset($options['product_id'])){
            $select->where("product_id = '" . $options['product_id'] . "'");
        }
        
        $select->where("imported_at is not null");
        $select->where("exported_at is null");

        $select->having("date_at = '" . $options['date_at'] . "'");
        
        $select->order('serial asc');
        try{
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return 0;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return [];
        }
        return $arr;
    }
    
    /**
     * update more than one row
     */
    public function updatesRowAsCondition($companyId = null, $options = null){
        if(!isset($options['data'])) return 0;
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $update = $sql->update($this->_name . "_" . strtolower($companyId));
        $update->set($options['data']);
        if(isset($options['is_serial'])){
            if($options['is_serial'] == '1'){
                $update->where("qrcode = '" . $options['value_begin'] . "'");
            }elseif($options['is_serial'] == '2'){
                $update->where("serial = '" . $options['value_begin'] . "'");
            }elseif($options['is_serial'] == '3'){
                $update->where("serial >= '" . $options['value_begin'] . "'");
                $update->where("serial <= '" . $options['value_end'] . "'");
            }else{
                return 0;
            }
        }else{
            return 0;
        }
        try {
            $statement = $sql->prepareStatementForSqlObject($update);
            // \Zend\Debug\Debug::dump($statement); die();
            //$selectString = $sql->buildSqlString($update);
           // echo $selectString;
            $results = $statement->execute();
            return $results->count();
        } catch (Exception $e) {
            return 0;
        }
        if(empty($results)){
            return 0;
        }
        return 0;
    }

    public function fetchRowAsCondition($companyId = null,$options =null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name . "_" . strtolower(\Admin\Service\Authentication::getCompanyId()));
        if(isset($options['is_serial'])){
            if($options['is_serial'] == '1'){
                $select->where("qrcode = '" . $options['value_begin'] . "'");
            }elseif($options['is_serial'] == '2'){
                $select->where("serial = '" . $options['value_begin'] . "'");
            }elseif($options['is_serial'] == '3'){
                $select->where("serial >= '" . $options['value_begin'] . "'");
                $select->where("serial <= '" . $options['value_end'] . "'");
            }else{
                return 0;
            }
        }else{
            return 0;
        }
        try {
            $statement = $sql->prepareStatementForSqlObject($select);
            //\Zend\Debug\Debug::dump($statement); die();
            //$selectString = $sql->buildSqlString($update);
           // echo $selectString;
            $results = $statement->execute();
            return $results->count();
        } catch (Exception $e) {
            return 0;
        }
        if(empty($results)){
            return 0;
        }
        return 0;
    }
    
    public function addData($options){
        $adapter = $this->adapter;
        // get data in options
        $blockId = $options['blocks_id'];
        $productsId = $options['products_id'];
        $numberSerial = $options['number_serial'];
        $numberCreated = $options['number_created'];
        $isQrcode = $options['is_qrcode'];
        $serialBegin = $options['serial_begin'];
        $prefixCode = $options['prefix_code'];
        $prefixSerial = $options['prefix_serial'];

        $entityCodeRoots = new CodeRoots($adapter);
        $entityCodeRootsQrcode = new CodeRootsQrcode($adapter);
        $limitGet = 10000;
        // check $numberCreated
        while($numberCreated > 0){
            $sql = "insert into `codes_" . strtolower($options['company_id']) . "` (`id`, `company_id`, `serial`, `qrcode`, `block_id`, `product_id`) values";
            // get number
            $number = ($numberCreated < $limitGet) ? $numberCreated : $limitGet;
            // change $numberCre
            $numberCreated -= $limitGet;
            // get codes from table code_roots
            $arrCodeRoots = $entityCodeRoots->fetchAllLimit($number);
            // check if is qrcode
            if($isQrcode == 1){
                $arrCodeRootsQrcode = $entityCodeRootsQrcode->fetchAllLimit($number);
            }
            $strDelete = '';
            $strDeleteQrcode = '';
            $i = 0;
            while($number > 0){
                $number--;
                // set str delete
                if($strDelete == ''){
                    $strDelete .= "'" . $arrCodeRoots[$number]['id'] . "'";
                }else{
                    $strDelete .= ",'" . $arrCodeRoots[$number]['id'] . "'";
                }
                // update serial
                $serial = $this->editSerial($prefixSerial, $numberSerial, $serialBegin);
                $serialBegin++;
                // update id
                $id = $prefixCode . $arrCodeRoots[$number]['id'];
                // update qrcode
                $qrcode = '';
                if($isQrcode == 1){
                    // set str delete qrcode
                    if($strDeleteQrcode == ''){
                        $strDeleteQrcode .= "'" . $arrCodeRootsQrcode[$number]['id'] . "'";
                    }else{
                        $strDeleteQrcode .= ",'" . $arrCodeRootsQrcode[$number]['id'] . "'";
                    }
                    $qrcode = $arrCodeRootsQrcode[$number]['id'];
                }
                // update ','
                if($i != 0){
                    $sql .= ', ';
                }
                $i++;
                $sql .= "('" . $id . "', '" . $options['company_id'] . "', '" . $serial . "', '" . $qrcode . "', '" . $blockId . "', " . (($productsId == null) ? "null" : ("'" . $productsId . "'")) . ")";
            }
            // update ';'
            $sql .= ';';
            try {
                $statement = $adapter->query($sql);
                $result = $statement->execute();
            } catch (Exception $e) {
                throw new RuntimeException('Error add pin!');
            }
            $entityCodeRoots->deleteRows($strDelete);
            if($isQrcode == 1){
                $entityCodeRootsQrcode->deleteRows($strDeleteQrcode);
            }
            unset($sql);
            unset($strDelete);
            unset($strDeleteQrcode);
            unset($arrCodeRoots);
            unset($arrCodeRootsQrcode);
        }
        // return true if code come here
        return true;
    }
    
    private function editSerial($prefixSerial, $numberSerial, $serialBegin){
        for($i = strlen($serialBegin); $i < $numberSerial; $i++){
            $serialBegin = '0' . $serialBegin;
        }
        return $prefixSerial . $serialBegin;
    }
    
    public function fetchRowAsQrcode($qrcode = null, $companyId = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name . "_" . strtolower($companyId));
        $select->where("qrcode = '" . $qrcode . "'");
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
        return $arr[0];
    }
    
    /**
     * Update value number_checked increase
     */
    public function updateRowAsQrcode($qrcode, $data, $companyId = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $update = $sql->update($this->_name . "_" . strtolower($companyId));
        $update->set($data);
        $update->where(['qrcode' => $qrcode]);
        try {
            $statement = $sql->prepareStatementForSqlObject($update);
            $results = $statement->execute();
        } catch (Exception $e) {
            return false;
        }
        if(empty($results)){
            return false;
        }
        return true;
    }
    
    /**
     * Update value number_checked increase
     */
    public function updateRowAsCondition($companyId = null, $key, $value, $data){
        //\Zend\Debug\Debug::dump($data); die();
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $update = $sql->update($this->_name . "_" . strtolower($companyId));
        $update->set($data);
        $update->where([$key => $value]);
        try {
            $statement = $sql->prepareStatementForSqlObject($update);
            $results = $statement->execute();
        } catch (Exception $e) {
            return false;
        }
        if(empty($results)){
            return false;
        }
        return true;
    }
    
    public function fetchRowId($companyId = null, $id){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name . '_' . strtolower($companyId));
        $select->where("`id` = '$id'");
        $select->limit(1);
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
    
    public function fetchCount($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->columns(['total' => new Expression('count(*)')]);
        if(isset($options['product_id'])){
            $select->where("products_id = '" . $options['product_id'] . "'");
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
    
    public function runSql($sql){
        if($sql == "") return true;
        $adapter = $this->adapter;
        try {
            $statement = $adapter->query($sql);
            $result = $statement->execute();
        } catch (Exception $e) {
            throw new RuntimeException('Error, contact administrator!');
        }
        return true;
    }

    public function fetchOneRowAsSerial($companyId = null,$serial){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name . "_" . strtolower(\Admin\Service\Authentication::getCompanyId()));
        $select->where("serial = '".$serial."'");
        try {
            $selectString = $sql->buildSqlString($select);
            //\Zend\Debug\Debug::dump($selectString); die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return 0;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return [];
        }
        return $arr;
    }

    public function fetchRowAsProduct($companyId = null,$options = null,$product){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name . "_" . strtolower(\Admin\Service\Authentication::getCompanyId()));
        if(isset($options['is_serial'])){
            if($options['is_serial'] == '1'){
                $select->where("qrcode = '" . $options['value_begin'] . "'");
            }elseif($options['is_serial'] == '2'){
                $select->where("serial = '" . $options['value_begin'] . "'");
            }elseif($options['is_serial'] == '3'){
                $select->where("serial >= '" . $options['value_begin'] . "'");
                $select->where("serial <= '" . $options['value_end'] . "'");
            }else{
                return 0;
            }
        }else{
            return 0;
        }
        $select->where("product_id = '".$product."'");
        try {
            $selectString = $sql->buildSqlString($select);
            //\Zend\Debug\Debug::dump($selectString); die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
            return $results->count();
        } catch (Exception $e) {
            return 0;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return [];
        }
        return $arr;
    }
    
}