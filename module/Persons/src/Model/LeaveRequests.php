<?php

namespace Persons\Model;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class LeaveRequests{

    protected $_name = 'leave_requests';
    protected $_primary = array('id');

    private $adapter = null;

    public static function getStatus(){
	    return [
	        '0' => "<span class='text-warning'>Không duyệt</span>",
	        '1' => "<span class='info darken-1'>Đang chờ duyệt</span>",
	        '2' => "<span class='text-success'>Đã duyệt</span>",
	        '-1' => 'Đã xóa'
	    ];
	}

    public static function getOption(){
	    return [
            '0' => "",
	        '1' => "Sáng",
	        '2' => "Chiều",
	    ];
	}

    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    public function fetchAlls($options = null){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("user_id is not null");
        $select->where("profile_id is not null");
        $select->where("`status` != -1");
        if(isset($options['keyword'])){
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
        $select->order("leave_start_date desc");
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

    public function fetchAllByUserId($userId, $options = null){
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("user_id = '$userId'");
        $select->where("profile_id is not null");
        if(isset($options['datetime_begin'])){
            if($options['datetime_begin'] != ''){
                $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $options['datetime_begin']);
                $select->where("`leave_start_date` >= '" . date_format($datetimeBegin, 'Y-m-d H:i:s') . "'");
            }
        }
        if(isset($options['datetime_end'])){
            if($options['datetime_end'] != ''){
                $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $options['datetime_end']);
                $select->where("`leave_start_date` <= '" . date_format($datetimeEnd, 'Y-m-d H:i:s') . "'");
            }
        }
        $select->where("`status` != -1");
        $select->order("created_at desc");
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

    public function fetchRowById($id){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("id = '$id'");
        try {
            $selectString = $sql->buildSqlString($select);
           // echo $selectString; die();
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
            return $results;
        } catch (Exception $e) {
            return [];
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
            // echo $sql->buildSqlString($update); die();
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

    public function fetchAllAnnualLeaveByUserId($userId){
        $year = date('Y') - 1;
        // $year = 2022 - 1;
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("user_id = $userId");
        $select->where("`status` = 2");
        $select->where("`annual_leave` = '$year'");
        $select->order("created_at desc");
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
        $optionLeaveRequest = \Persons\Model\LeaveRequests::getOption();
        // [$item['option_leave_start_date']]
        $re = $results->toArray();
        $arr = [];
        foreach($re as $item){
            $arr[$item['id']] = date_format(date_create($item['leave_start_date']), "d/m/Y") ." ".$optionLeaveRequest[$item['option_leave_start_date']] . "-" . " " .date_format(date_create($item['leave_stop_date']), "d/m/Y") . " ".$optionLeaveRequest[$item['option_leave_stop_date']];
        }
        return $arr;
    }

    public function fetchAllOptions($option = null){
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("status = 2");
        if(isset($option['datetime_begin']) && isset($option['datetime_end'])){
            $datetimeBegin = date_format(date_create_from_format('d/m/Y H:i:s', $option['datetime_begin']),'Y-m-d 00:00:00');
            $datetimeEnd = date_format(date_create_from_format('d/m/Y H:i:s', $option['datetime_end']),'Y-m-d 23:59:59');
            $select->where("((leave_start_date >= '".$datetimeBegin."' and leave_start_date <= '".$datetimeEnd."') or (leave_stop_date >= '".$datetimeBegin."' and leave_stop_date <= '".$datetimeEnd."'))");
        }
        $select->order("user_id asc");
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