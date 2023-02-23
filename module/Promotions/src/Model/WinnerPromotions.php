<?php

namespace Promotions\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;

class WinnerPromotions
{
    protected $_name = 'winner_promotions';
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

        $select->where($this->_name.".company_id = '" . COMPANY_ID . "'");

        if (isset($options['promotion_id'])) {
            if ($options['promotion_id'] != "" && $options['promotion_id'] != null) {
                $select->where("promotion_id = '" . $options['promotion_id'] . "'");
            }
        }
        if (isset($options['phone_id'])) {
            $select->where("phone_id = '" . $options['phone_id'] . "'");
        }
        if (isset($options['user_input'])) {
            if ($options['user_input'] != "") {
                $select->where("user_input = '" . $options['user_input'] . "'");
            }
        }
        if (isset($options['user_id'])) {
            if ($options['user_id'] != "") {
                $select->where("user_input = '" . $options['user_id'] . "'");
            }
        }
        if (isset($options['user_crm_id'])) {
            if ($options['user_crm_id'] != "") {
                $select->where("user_input_id = '" . $options['user_crm_id'] . "'");
            }
        }
        if (isset($options['keyword'])) {
            if ($options['keyword'] != '') {
                $select->where("`phone_id` like '%" . $options['keyword'] . "%'");
            }
        }
        if (isset($options['status_order'])) {
            $select->where("status_order = '" . $options['status_order'] . "'");
            if ($options["status_order"] == '1') {
                if (isset($options['datetime_begin'])) {
                    if ($options['datetime_begin'] != '') {
                        $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $options['datetime_begin']);
                        $datetimeBegin = date_format($datetimeBegin, 'Y-m-d H:i:s');
                        $select->where("finished_at >= '" . $datetimeBegin . "'");
                    }
                }
                if (isset($options['datetime_end'])) {
                    if ($options['datetime_end'] != '') {
                        $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $options['datetime_end']);
                        $datetimeEnd = date_format($datetimeEnd, 'Y-m-d H:i:s');
                        $select->where("finished_at <= '" . $datetimeEnd . "'");
                    }
                }
            }
        }
		 $select->order('created_at desc');
		if (isset($options['is_join'])){
			$select->join('messages', 'messages.phone_id = winner_promotions.phone_id',['content_in'], 'left');
			$select->join('agents', 'messages.agent_id = agents.id', ['name'],'left');}
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

    public function fetchCount($options = null)
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->columns(['total' => new Expression('count(*)')]);
        if (isset($options['promotion_id'])) {
            $select->where("promotion_id = '" . $options['promotion_id'] . "'");
        }
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return 0;
        }
        $arr = $results->toArray();
        if (empty($arr)) {
            return 0;
        }
        return $arr[0]['total'];
    }

    public function fetchTotalAsPromotions($promotionId = null)
    {
        if ($promotionId == null) return 0;
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->columns(['total' => new Expression('count(*)')]);
        $select->where("promotion_id = '" . $promotionId . "'");
        try {
            $selectString = $sql->buildSqlString($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return 0;
        }
        $arr = $results->toArray();
        if (empty($arr)) {
            return 0;
        }
        return $arr[0]['total'];
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
            return $results->getGeneratedValue();
        } catch (Exception $e) {
            return false;
        }
        if (empty($results)) {
            return false;
        }
        return false;
    }

    public function deleteRows($conditions = [])
    {
        if (empty($conditions)) return false;
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $delete = $sql->delete($this->_name);
        if (isset($conditions['promotion_id'])) {
            $delete->where(['promotion_id' => $conditions['promotion_id']]);
        }
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

    public function fetchRow($id)
    {
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
        if (empty($arr)) {
            return false;
        }
        return $arr[0];
    }
    public function fetchRowByPhoneId($id, $promotion_id)
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        $select->where("phone_id = '$id'");
        $select->where("promotion_id = '$promotion_id'");
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
}
