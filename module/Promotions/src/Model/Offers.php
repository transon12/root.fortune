<?php

/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Promotions\Model;

use Exception;
use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Settings\Model\Wards;

class Offers
{
    protected $_name = 'offers';
    protected $_primary = array('id');

    const REQUEST_GOP_MA    = 0;
    const REQUEST_TACH_MA   = 1;
    const REQUEST_CAP_MA    = 2;
    const REQUEST_GOP_SDT   = 3;
    const REQUEST_EDIT_SDT  = 4;
    const REQUEST_CHUYEN_SP = 5;
    public static function returnRequest()
    {
        return [
            self::REQUEST_GOP_MA    => "Gộp mã",
            self::REQUEST_TACH_MA   => "Tách mã",
            self::REQUEST_CAP_MA    => "Cấp mã",
            self::REQUEST_GOP_SDT   => "Gộp Sđt",
            self::REQUEST_EDIT_SDT  => "Edit số điện thoại",
            self::REQUEST_CHUYEN_SP => "Chuyển sản phẩm"
        ];
    }

    const RESPONSE_CHO_XU_LY            = 0;
    const RESPONSE_DUYET_TRA_THUONG     = 1;
    const RESPONSE_DUYET_CAP_MA         = 2;
    const RESPONSE_DUYET_GOP            = 3;
    const RESPONSE_MKT_DUYET            = 4;
    const RESPONSE_KHONG_HO_TRO         = -1;
    public static function returnResponse()
    {
        return [
            self::RESPONSE_CHO_XU_LY            => "Treo chờ xử lý",
            self::RESPONSE_DUYET_TRA_THUONG     => "Duyệt trả thưởng",
            self::RESPONSE_DUYET_CAP_MA         => "Duyệt cấp mã",
            self::RESPONSE_DUYET_GOP            => "Duyệt gộp",
            self::RESPONSE_MKT_DUYET            => "MKT duyệt",
            self::RESPONSE_KHONG_HO_TRO         => "Không hỗ trợ"
        ];
    }

    private $adapter = null;
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAlls($options = null)
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);

        if (isset($options['keyword'])) {
            $select->where("`staff` like '%" . $options['keyword'] . "%' or 
            `phone` like '%" . $options['keyword'] . "%' or 
            `info` like '%" . $options['keyword'] . "%' or 
            `content` like '%" . $options['keyword'] . "%'");
        }
        $select->where("status != -1");
        $select->order('id desc');
        try {
            $selectString = $sql->buildSqlString($select);
            //echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return [];
        }
        if (empty($results)) {
            return [];
        }
        return $results->toArray();
    }

    public function fetchRow($id)
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("id = '$id'");
        $select->where("status != -1");
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        $arr = $results->toArray();
        if (empty($arr)) {
            return false;
        }
        return $arr[0];
    }

    public function addRow($data)
    {
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
        if (empty($results)) {
            return false;
        }
        return false;
    }

    public function updateRow($id, $data)
    {
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
        if (empty($results)) {
            return false;
        }
        return false;
    }

    public function deleteRow($id)
    {
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
        if (empty($results)) {
            return false;
        }
        return false;
    }
}
