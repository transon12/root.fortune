<?php
namespace Admin\Model;

use RuntimeException;
use Settings\Model\Companies;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use Zend\Session\SessionManager;

class Users{
	protected $_name = 'users';
	protected $_primary = array('id');
	
	private $adapter = null;
	private $sessionContainer = null;
	
	public static function getStatus(){
	    return [
	        '0' => 'Đã khóa',
	        '1' => 'Đã kích hoạt',
	        '2' => 'Chưa kích hoạt',
	        '-1' => 'Đã xóa'
	    ];
	}
	
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $manager = new SessionManager();
        $this->sessionContainer = new Container('session_login', $manager);
    }
    
    public function fetchAlls($options = null){
        //\Zend\Debug\Debug::dump($options); die();
        //if($groupsId == null) return [];
       // echo \Admin\Service\Authentication::getCompanyId(); die();
       //echo \Admin\Service\Authentication::getCompanyId(); die();
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->quantifier(\Zend\Db\Sql\Select::QUANTIFIER_DISTINCT);
        if($this->sessionContainer->id != "1"){
            $select->where($this->_name . ".id != 1");
            $select->where($this->_name . ".status != '-1'");

            if(empty(\Admin\Service\Authentication::getCompanyId())){
                //$select->where("company_id =''");
                
                $entityCompanies = new Companies($adapter);
                $optionsCompanies = $entityCompanies->fetchRowUserId($this->sessionContainer->id);
               // \Zend\Debug\Debug::dump($optionsCompanies); die();
                
                if(!empty($optionsCompanies)){
                    $arrUsers= $this->fetchRowAsCompanies($optionsCompanies);
                    //\Zend\Debug\Debug::dump($arrUsers); die();
                    //$j=count($arrUsers)-1;
                    $i=0;
                    foreach($arrUsers as $item){
                        $se[$i] = "id = '".$item['id']."'";
                        $i++;
                    }
                    $seString = implode(' or ',$se); 
                    $select->where("company_id ='' or $seString");
                }else{
                    $select->where("company_id =''");
                }
            }
        }
        if(isset($options['status']) && $options['status'] != ''){
            $select->where($this->_name . ".status = '" . $options['status'] . "'");
        }
        
        if(isset($options['company_id']) && $options['company_id'] != null && $options['company_id'] != ""){
            $select->where("company_id = '" . $options['company_id'] . "'");
         }
        
        if(isset($options['keyword'])){
            $select->where("(firstname like '%" . $options['keyword'] . "%' or 
                lastname like '%" . $options['keyword'] . "%' or 
                username like '%" . $options['keyword'] . "%')");
        }

//         $select->join('groups_positions_users', 'users.id = groups_positions_users.user_id', [], 'left');
        //$select->join('companies', 'users.company_id = companies.id', ['is_group'], 'left');
     
        //\Zend\Debug\Debug::dump($currentCompany); die();
        // if(empty($currentCompany)){
        //     // Default, user can see group, position, and permission 
        //     $currentCompany['is_group'] = 1;
        // }
        $strGroupsId = implode(',', $options['groups_id']);
//         if(!empty($options['groups_id']) && !in_array('1', $options['groups_id'])){
//             // get all groups lower right
//             $entityGroups = new Groups($adapter);
//             $strGroups = "'" . implode("', '", $entityGroups->fetchAllOptions1(['min_level' => $this->sessionContainer->groups_min_level])) . "'";
//             $entityPositions = new Positions($adapter);
//             $strPositions = "'" . implode("', '", $entityPositions->fetchAllOptions1( ['min_level' => $this->sessionContainer->positions_min_level] )) . "'";
//             //echo $strPositions; die();
//             $select->where('group_id in (' . $strGroupsId . ') 
//                 OR id not in (select user_id from groups_positions_users group by user_id) 
//                 OR groups_positions_users.group_id in (' . $strGroups . ')');
//         }
        $select->order($this->_name . '.created_at desc');
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
        return $results->toArray();
    }
    
    public function fetchRow($id){
        $adapter = $this->adapter;
        
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where($this->_name . ".id = '$id'");
        if($this->sessionContainer->id != "1"){
            $select->where($this->_name . ".id != 1");
        }
        $select->join('companies', 'users.company_id = companies.id', ['is_group'], 'left');
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
    
    public function fetchRowAsUsername($username){
        $adapter = $this->adapter;
        
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("username = '$username'");
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
    
    public function updateRow($id, $data){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $update = $sql->update($this->_name);
        $update->set($data);
        $update->where(['id' => $id]);
        try {
            $statement = $sql->prepareStatementForSqlObject($update);
            //echo $sql->buildSqlString($update); die();
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
    
    public function fetchAllOptions($options = null){
        //\Zend\Debug\Debug::dump($options); die();
        //if($groupsId == null) return [];
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        if($this->sessionContainer->id != "1"){
            $select->where("id != 1");
            $select->where("status != '-1'");
        }
        
        if(isset($options['company_id']) && $options['company_id'] != null){
            $select->where("company_id = '" . $options['company_id'] . "'");
        }
        
        $select->order('firstname asc');
        try {
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
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
            $res[$item['id']] = $item['firstname'] . ' ' . $item['lastname'] . ' (' . $item['username'] . ')';
        }
        return $res;
    }

    public function fetchRowAsCompanies($options=null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $j=count($options)-1;
        $select->where("");
        $a =0;
        try {
            $selectString = $sql->buildSqlString($select);
            for($i=0; $i <= $j; $i++){
                $selectString .= " company_id = '".$options[$i]."'";
                if($a<$j){
                    $selectString .= " or";
                }
                $a++;
            }
            //echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        if(empty($results)){
            return false;
        }
        return $results->toArray();
    }

    public function fetchUserAsCompanies(){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = '".""."'");
        $select->where("status = 1");
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return false;
        }
        if(empty($results)){
            return [];
        }
        $re = $results->toArray();
        $arr = [];
        foreach($re as $item){
            $arr[$item['id']] = $item['lastname']. " ". $item['firstname'];
            // $arr[$item['id']] = $item['username'];
        }
        //\Zend\Debug\Debug::dump($arr); die();
        return $arr;
    }

    public function fetchRowAsId($id){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("id = '".$id."'");
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return false;
        }
        $arr = $results->toArray();
        if(empty($arr)){
            return false;
        }
        return $arr[0];
    }

    public function fetchOptionAvatar($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("status = 1");
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return false;
        }
        if(empty($results)){
            return [];
        }
        $re = $results->toArray();
        $arr = [];
        foreach($re as $item){
            $arr[$item['id']] = $item['avatar'];
        }
        //\Zend\Debug\Debug::dump($arr); die();
        return $arr;
    }

    public function fetchOptionGender($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("status = 1");
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return false;
        }
        if(empty($results)){
            return [];
        }
        $re = $results->toArray();
        $arr = [];
        foreach($re as $item){
            $arr[$item['id']] = $item['gender'];
        }
        //\Zend\Debug\Debug::dump($arr); die();
        return $arr;
    }

    public function fetchUserName($options = null){
        //\Zend\Debug\Debug::dump($options); die();
        //if($groupsId == null) return [];
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = ''");
        if($this->sessionContainer->id != "1"){
            $select->where("id != 1");
        }
        $select->where("status != '-1'");
        $select->order('created_at asc');
        try {
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
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
            $res[$item['id']] = $item['lastname'] . ' ' . $item['firstname'] . ' (' . $item['username'] . ')';
        }
        return $res;
    }

    public function fetchOptions($options = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = '".""."'");
        $select->where("status = 1");
        try{
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        }catch(Exception $e){
            return false;
        }
        if(empty($results)){
            return [];
        }
        $re = $results->toArray();
        $arr = [];
        foreach($re as $item){
            $arr[$item['id']] = ['avatar' => $item['avatar'], 'name' => $item['lastname'] . ' ' . $item['firstname'], 'gender' => $item['gender']];
        }
        //\Zend\Debug\Debug::dump($arr); die();
        return $arr;
    }
}