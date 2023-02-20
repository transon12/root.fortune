<?php

namespace Settings\Model;

use Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;

class StatisticChecks
{

    protected $_name = 'statistic_checks';
    protected $_primary = array('id');

    private $adapter = null;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }


    /**
     * Lấy số lượng nhắn tin đúng ngày hiện tại
     */
    public function fetchTotalSuccessDateCurrent($companyId = ""){
        if($companyId == ""){
            return 0;
        }
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(["date" => "date", "total" => new Expression('SUM(number_success)')]);
        $select->from($this->_name);
        $select->where("`company_id` = '" . $companyId . "'");
        $date = \Pxt\Datetime\ChangeDatetime::getDateCurrent();
        $select->where("`date` = '" . $date . "'");
        $select->group("date");
        try {
            $selectString = $sql->buildSqlString($select);
            // echo $selectString; die();
            $results = $this->adapter->query($selectString, $this->adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return 0;
        }
        $re = $results->toArray();
        if (empty($re)) {
            return 0;
        }
        return $re[0]["total"];
    }
    /**
     * Lấy số lượng tất cả tin nhắn đúng
     */
    public function fetchTotalSuccessAll($companyId = ""){
        if($companyId == ""){
            return 0;
        }
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(["date" => "date", "total" => new Expression('SUM(number_success)')]);
        $select->from($this->_name);
        $select->where("`company_id` = '" . $companyId . "'");
        // $date = \Pxt\Datetime\ChangeDatetime::getDateCurrent();
        // $select->where("`date` = '" . $date . "'");
        // $select->group("date");
        try {
            $selectString = $sql->buildSqlString($select);
            // echo $selectString; die();
            $results = $this->adapter->query($selectString, $this->adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return 0;
        }
        $re = $results->toArray();
        if (empty($re)) {
            return 0;
        }
        return $re[0]["total"];
    }
    /**
     * Xử lý cập nhật số lượng kiểm tra tem
     * Trong đó status bao gồm 3 trạng thái:
     * 0: mã sai
     * 1: mã đúng
     * 2: mã được kiểm tra
     */
    public function handleNumber($companyId = "", $status = 0){
        if($companyId == ""){
            return false;
        }
        // Kiểm tra ngày này đã có hay chưa?
        $date = \Pxt\Datetime\ChangeDatetime::getDateCurrent();
        $row = $this->fetchRow($companyId, $date);
        if(empty($row)){
            $id = $this->addRow([
                "company_id"    => $companyId,
                "date"          => $date,
                "created_at"    => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
            ]);
            if($id != 0){
                $row = $this->fetchRow($companyId, $date);
            }else{
                return false;
            }
        }
        $dataUpdate = [
            "updated_at"    => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
        ];
        if($status == 2){
            $dataUpdate["number_checked"] = (int)$row["number_checked"] + 1;
        }elseif($status == 1){
            $dataUpdate["number_success"] = (int)$row["number_success"] + 1;
        }else{
            $dataUpdate["number_invalid"] = (int)$row["number_invalid"] + 1;
        }
        $this->updateRow($row["id"], $dataUpdate);
        return true;
    }
    /**
     * Kiểm tra ngày đã tồn tại hay chưa
     */
    public function  fetchRow($companyId = "", $date = ""){
        if($companyId == "" || $date == ""){
            return [];
        }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("company_id = '" . $companyId . "'");
        $select->where("date = '" . $date . "'");
        $select->limit(1);
        try {
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
            $results = $this->adapter->query($selectString, $this->adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return [];
        }
        $arr = $results->toArray();
        if (empty($arr)) {
            return [];
        }
        return $arr[0];
    }
    /**
     * Thêm dữ liệu
     */
    public function addRow($data){
        $sql = new Sql($this->adapter);
        $insert = $sql->insert($this->_name);
        $insert->values($data);
        try {
            $statement = $sql->prepareStatementForSqlObject($insert);
            $results = $statement->execute();
            return $results->getGeneratedValue();
        } catch (Exception $e) {
            return 0;
        }
        return 0;
    }
    /**
     * Sửa dữ liệu
     */
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
            return [];
        }
        if (empty($results)) {
            return [];
        }
        return false;
    }

}
