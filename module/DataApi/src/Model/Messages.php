<?php

namespace DataApi\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Exception;

class Messages
{
    protected $_name = 'messages';
    protected $_primary = array('id');

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
        if (isset($options['company_id']) && $options['company_id'] != null) {
            $select->where("`company_id` = '" . $options['company_id'] . "'");
        }else{
            return [];
        }
        if (isset($options['keyword'])) {
            if ($options['keyword'] != '') {
                $select->where("(`code_id` like '%" . $options['keyword'] . "%' or 
                    `code_serial` like '%" . $options['keyword'] . "%' or 
                    `code_qrcode` like '%" . $options['keyword'] . "%' or 
                    `phone_id` like '%" . $options['keyword'] . "%' or 
                    `message_in` like '%" . $options['keyword'] . "%')");
            }
        }
        if (isset($options['type']) && $options['type'] != '') {
            $select->where("`type` = '" . $options['type'] . "'");
        }
        if (isset($options['datetime_begin'])) {
            if ($options['datetime_begin'] != '') {
                $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $options['datetime_begin']);
                $select->where("`created_at` >= '" . date_format($datetimeBegin, 'Y-m-d H:i:s') . "'");
            }
        }
        if (isset($options['datetime_end'])) {
            if ($options['datetime_end'] != '') {
                $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $options['datetime_end']);
                $select->where("`created_at` <= '" . date_format($datetimeEnd, 'Y-m-d H:i:s') . "'");
            }
        }
        if (isset($options['page']) && isset($options["limit"])) {
            $select->limit($options["limit"]);
            $select->offset(((int)$options["page"] - 1) * (int)$options["limit"]);
        }

        $select->order('created_at desc');
        try {
            $selectString = $sql->buildSqlString($select);
            // echo $selectString; die();
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        if (empty($results)) {
            return false;
        }
        return $results->toArray();
    }
}
