<?php

namespace Admin\Model;

use Exception;
use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Settings\Model\Logs;
use Settings\Model\Companies;
use Settings\Model\Settings;

class PxtAuthentication
{

    private $adapter = null;
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function checkUserExist($username = '', $password = '')
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('users');
        $select->where("username = '$username'");
        $select->where("password = '$password'");
        try {
            $selectString = $sql->buildSqlString($select);
            // return $selectString;
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return false;
        }
        $arr = $results->toArray();
        if (empty($arr)) {
            return false;
        }
        $result = $arr[0];
        //\Zend\Debug\Debug::dump($result) ; die();
        // check status
        if ($result['id'] != '1' && $result['status'] == '0') {
            return false;
        }
        $arrPermissions = [];
        $arrNotPermissions = [];
        $entityMcas = new Mcas($adapter);
        $arrMcas = $entityMcas->fetchAllAsLevel(1, null, null);
        $result['groups_id'] = [];
        // get users permission is allow
        $entityMcasUsersAllow = new McasUsersAllow($adapter);
        $arrMcasUsersAllow = $entityMcasUsersAllow->fetchRowAsUser($result['id']);
        if (!empty($arrMcasUsersAllow)) {
            foreach ($arrMcasUsersAllow as $item) {
                $arrPermissions[$item['mca_id']] = $item['mca_id'];
            }
        }
        // get users not permission
        if ($result['id'] != "1") {
            if (!empty($arrMcas)) {
                foreach ($arrMcas as $itemMcas) {
                    if (!empty($itemMcas['child'])) {
                        foreach ($itemMcas['child'] as $item) {
                            if (!isset($arrPermissions[$item['id']])) {
                                $arrNotPermissions[$item['id']] = $itemMcas['code'] . '/' . $item['code'];
                            }
                        }
                    }
                }
            }
        }
        // get level min's user in groups, positions
        $result['groups_min_level'] = '0';
        $result['groups_max_level'] = '0';
        $result['positions_min_level'] = '0';
        $result['positions_max_level'] = '0';
        $result['permissions'] = $arrPermissions;
        $result['not_permissions'] = $arrNotPermissions;
        return $result;
    }

    public function checkUserExist1($username = '', $password = '')
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('users');
        $select->where("username = '$username'");
        $select->where("password = '$password'");
        try {
            $selectString = $sql->buildSqlString($select);
            // return $selectString;
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return [];
        }
        $arr = $results->toArray();
        if (empty($arr)) {
            return [];
        }
        $result = $arr[0];
        // check status
        if ($result['id'] != '1' && $result['status'] == '0') {
            return [];
        }
        return $result;
    }

    public function checkUserExist2($username = '', $password = '')
    {
        $adapter = $this->adapter;
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('users');
        $select->where("username = '$username'");
        $select->where("password = '$password'");
        try {
            $selectString = $sql->buildSqlString($select);
            // return $selectString;
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        } catch (Exception $e) {
            return [];
        }
        $arr = $results->toArray();
        if (empty($arr)) {
            return [];
        }
        $result = $arr[0];
        return $result;
    }

    public function fetchActionPresent($codeParent = null, $codeChild = null)
    {
        if ($codeParent == null || $codeChild == null) {
            return false;
        }
        $entityMcas = new Mcas($this->adapter);
        $arrMca = $entityMcas->fetchRowAsCode($codeParent);
        if (empty($arrMca)) {
            return false;
        }
        $arrMcaChild = $entityMcas->fetchRowAsCode($codeChild, $arrMca['id']);
        if (empty($arrMcaChild)) {
            return false;
        }
        return $arrMcaChild;
    }

    /**
     * add log
     */
    public function addLog($data)
    {
        $entityLogs = new Logs($this->adapter);
        $entityLogs->addRow($data);
    }

    /**
     * fetch config
     */
    public function fetchSetting($code = null)
    {
        if ($code == null) return [];
        $entitySettings = new Settings($this->adapter);
        $arrSetting = $entitySettings->fetchRow($code);
        if (!empty($arrSetting)) {
            $content = json_decode($arrSetting['content'], true);
            return $content;
        }
        return [];
    }
}
