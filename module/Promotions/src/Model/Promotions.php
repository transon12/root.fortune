<?php

namespace Promotions\Model;

use RuntimeException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Storehouses\Model\Products;

class Promotions
{
    protected $_name = 'promotions';
    protected $_primary = ['id'];

    private $adapter = null;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    const IS_TYPE_DIAL = 0;
    const IS_TYPE_SCORE = 1;
    const IS_TYPE_RANDOM = 2;

    public static function returnIsType()
    {
        return [
            self::IS_TYPE_DIAL => "Nhận danh sách quay số",
            self::IS_TYPE_SCORE => "Tích luỹ điểm",
            self::IS_TYPE_RANDOM => "Trúng ngẫu nhiên",
        ];
    }

    public function fetchAlls($options = null)
    {
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        // check company id
        if (isset($options['company_id']) && $options['company_id'] != null) {
            $select->where("company_id = '" . $options['company_id'] . "'");
        }
        // check 'keyword' exist
        if (isset($options['keyword'])) {
            $select->where("name like '%" . $options['keyword'] . "%'");
        }
        // check 'status' exist
        if (isset($options['status'])) {
            $select->where("status = '" . $options['status'] . "'");
        }
        // check 'time_now' exist
        if (isset($options['time_now'])) {
            $select->where("datetime_begin <= '" . $options['time_now'] . "'");
            $select->where("datetime_end >= '" . $options['time_now'] . "'");
        }
        // check 'is_agent' exist
        if (isset($options['is_agent'])) {
            $select->where("is_agent = " . $options['is_agent']);
        }


        if (isset($options['agent_id'])) {
            $select->where("agent_id = " . $options['agent_id']);
        }


        // check 'product_id' exist
        if (isset($options['product_id'])) {
            $select->join('promotions_products', 'promotions.id = promotions_products.promotion_id', ['product_id', 'score']);
            $select->where("product_id = '" . $options['product_id'] . "'");
        }
        // check 'dial' exist
        if (isset($options['dial'])) {
            if ($options['dial'] == 1) {
                $dial = '"dial":"1"';
                $select->where("(content like '%" . $dial . "%' or is_type = '" . self::IS_TYPE_DIAL . "')");
            }
        } else {
            $dial = '"dial":"1"';
            $select->where("(content like '%" . $dial . "%' or is_type = '" . self::IS_TYPE_DIAL . "')");
        }
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
        $re = $results->toArray();
        return $re;
    }

    public function fetchAllOptions01($options = null)
    {
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->_name);
        // check company id
        if (isset($options['company_id'])) {
            $select->where("company_id = '" . $options['company_id'] . "'");
        }
        // check 'keyword' exist
        if (isset($options['keyword'])) {
            $select->where("name like '%" . $options['keyword'] . "%'");
        }
        // check 'status' exist
        if (isset($options['status'])) {
            $select->where("status = '" . $options['status'] . "'");
        }
        // check 'time_now' exist
        if (isset($options['time_now'])) {
            $select->where("datetime_begin <= '" . $options['time_now'] . "'");
            $select->where("datetime_end >= '" . $options['time_now'] . "'");
        }
        // check 'is_agent' exist


        if (isset($options['is_agent'])) {
            $select->where("is_agent = " . $options['is_agent']);
        }

        if (isset($options['agent_id'])) {
            $select->where("agent_id = " . $options['agent_id']);
        }


        // check 'product_id' exist
        if (isset($options['product_id'])) {
            $select->join('promotions_products', 'promotions.id = promotions_products.promotion_id', ['products_id', 'score']);
            $select->where("products_id = '" . $options['product_id'] . "'");
        }
        // check 'dial' exist
        if (isset($options['dial'])) {
            if ($options['dial'] == 1) {
                $dial = '"dial":"1"';
                $select->where("content like '%" . $dial . "%' or is_type = " . self::IS_TYPE_DIAL);
            }
        } else {
            $dial = '"dial":"1"';
            $select->where("content like '%" . $dial . "%' or is_type = " . self::IS_TYPE_DIAL);
        }
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
        $re = $results->toArray();
        $res = [];
        foreach ($re as $item) {
            $res[$item['id']] = $item['name'];
        }
        return $res;
    }

    public function fetchRow($id)
    {
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
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

    public function addRow($data)
    {
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
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

    public function updateRow($id, $data)
    {
        $adapter = $this->adapter;
        $sql = new Sql($this->adapter);
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
        $sql = new Sql($this->adapter);
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

    /**
     * @param code_id: id code
     * @param product_id: id product
     * @param phone_id: phone number
     * @return array include: message, code_id, phone_id, price_topup (will continue top to phone_id)
     */

    public function checkPromotions($options = null)
    {
        if (!isset($options['code_id'])) return false;
        if (!isset($options['phone_id'])) return false;

        $codeId = $options['code_id'];
        $productId = isset($options['product_id']) ? $options['product_id'] : 0;
        $agentId = isset($options['agent_id']) ? $options['agent_id'] : 0;

        // get info product
        $entityProducts = new Products($this->adapter);
        $currentProduct = $entityProducts->fetchRow($productId);

        //	$entityAgents = new Agents($this->adapter);
        //    $currentAgent = $entityAgents->fetchRow($agentId);

        $arrPromotions = $this->fetchAlls([
            'status' => 1,
            'time_now' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
            'product_id' => $productId,
            'is_agent' => (isset($options['is_agent']) ? $options['is_agent'] : 0),
            //		'agent_id' => (isset($options['agent_id']) ? $options['agent_id'] : 0),
            'company_id' => $options['company_id'],
        ]);
        if (empty($arrPromotions) || count($arrPromotions) < 1) {
            return false;
        }
        //echo time();
        $i = 0;
        $results = [];
        foreach ($arrPromotions as $item) {
            $results[$i] = [
                'message' => '',
                'code_id' => $options['code_id'],
                'phone_id' => $options['phone_id'],
                'price_topup' => '',
            ];
            $params = json_decode($item['content'], true);
            if ($item['is_type'] == self::IS_TYPE_DIAL) {
                if ($params['input_type'] == 0) {
                    $this->handleTypeDial($item['id'], $options);
                }
                $i++;
            } else if ($item['is_type'] == self::IS_TYPE_SCORE) {
                // echo $item["name"] . " _ " . $item['score'] . "<br />";
                if ((int)$item['score'] !== 0) {
                    $reTypeScore = $this->handleTypeScore($item['id'], $options, $params, $item['score'], $currentProduct);
                    //\Zend\Debug\Debug::dump($updatePhone); die('zo');
                    $results[$i]['message'] = $reTypeScore['message'];
                    $results[$i]['price_topup'] = $reTypeScore['price_topup'];
                    $results[$i]['is_return'] = $reTypeScore['is_return'];
                    $isReturn = isset($reTypeScore['is_return']) ? $reTypeScore['is_return'] : '0';
                    if ($isReturn == '0') {
                        if ($params['dial'] == 1) {
                            $this->handleTypeDial($item['id'], $options);
                        }
                    }
                    $i++;
                } else {
                    unset($results[$i]);
                }
            } else if ($item['is_type'] == self::IS_TYPE_RANDOM) {
                if ($params['dial'] == 1) {
                    $this->handleTypeDial($item['id'], $options);
                }
                $reTypeRandom = $this->handleTypeRandom($item['id'], $options, $params);
                $results[$i]['message'] = $reTypeRandom['message'];
                $results[$i]['price_topup'] = $reTypeRandom['price_topup'];
                $i++;
            }
        }
        //\Zend\Debug\Debug::dump($arrPromotions); die();
        return $results;
    }

    private function handleTypeDial($promotionId = null, $options = null)
    {
        $entityListDials = new ListDials($this->adapter);
        $data = [
            'company_id' => $options['company_id'],
            'code_id' => $options['code_id'],
            'phone_id' => $options['phone_id'],
            'promotion_id' => $promotionId,
            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
        ];
        $entityListDials->addRow($data);
        return true;
    }

    private function handleTypeScore($promotionId = null, $options = null, $params = null, $score = 0, $currentProduct = [])
    {
        if ($params == null) return false;
        // record data
        $entityListPromotions = new ListPromotions($this->adapter);
        $entityWinnerPromotions = new WinnerPromotions($this->adapter);

        if ((int)$params["limit_message_day"] !== 0) {
            $dayBegin = date("d/m/Y", time()) . " 00:00:00";
            $dayEnd = date("d/m/Y", time()) . " 23:59:59";
            $arrListPromotionsLimit = $entityListPromotions->fetchAlls(['promotion_id' => $promotionId, 'phone_id' => $options['phone_id'],
                'datetime_begin' => $dayBegin, 'datetime_end' => $dayEnd]);
            $totalScoreListPromotionLimit = count($arrListPromotionsLimit);
            if ($totalScoreListPromotionLimit >= (int)$params["limit_message_day"]) {
                return [
                    'message' => $params['message_limit_day'],
                    'is_return' => 1,
                    'price_topup' => (isset($params['price_topup']) ? $params['price_topup'] : ''),
                ];
            }
        }
        if ((int)$params["limit_message_month"] !== 0) {
            $monthBegin = date("1/m/Y", time()) . " 00:00:00";
            $monthEnd = date("t/m/Y", time()) . " 23:59:59";
            $arrListPromotionsLimit = $entityListPromotions->fetchAlls(['promotion_id' => $promotionId, 'phone_id' => $options['phone_id'],
                'datetime_begin' => $monthBegin, 'datetime_end' => $monthEnd]);
            $totalScoreListPromotionLimit = count($arrListPromotionsLimit);
            if ($totalScoreListPromotionLimit >= (int)$params["limit_message_month"]) {
                return [
                    'message' => $params['message_limit_month'],
                    'is_return' => 1,
                    'price_topup' => (isset($params['price_topup']) ? $params['price_topup'] : ''),
                ];
            }
        }
        // add data
        $data = [
            'company_id' => $options['company_id'],
            'code_id' => $options['code_id'],
            'phone_id' => $options['phone_id'],

            'promotion_id' => $promotionId,
            'product_id' => $options['product_id'],
            'is_type' => self::IS_TYPE_SCORE,
            'score' => $score,

            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
            'agent_id' => $options['agent_id'],
        ];
        $entityListPromotions->addRow($data);
        // check condition
        // get data list promotions as phone_id and promotion_id
        $arrListPromotions = $entityListPromotions->fetchAlls(['promotion_id' => $promotionId, 'phone_id' => $options['phone_id']]);
        $totalScoreListPromotion = 0;
        if (!empty($arrListPromotions)) {
            foreach ($arrListPromotions as $item) {
                $totalScoreListPromotion = (int)$totalScoreListPromotion + (int)$item['score'];
            }
        }
        $winner = $entityWinnerPromotions->fetchRowByPhoneId($options['phone_id'], $promotionId);

        // check condition to win
        $scoreRemain = !$winner ? $totalScoreListPromotion : ($totalScoreListPromotion - $winner['score']);
        // set message
        $params['message_near_win'] = str_replace('{diem_san_pham}', $score, $params['message_near_win']);
        $params['message_near_win'] = str_replace('{ten_san_pham}', (isset($currentProduct['name']) ? $currentProduct['name'] : ''), $params['message_near_win']);
        $params['message_near_win'] = str_replace('{tong_diem}', $totalScoreListPromotion, $params['message_near_win']);
        $params['message_near_win'] = str_replace('{diem_gan_trung}', $scoreRemain, $params['message_near_win']);
        // check if only actulative
        if (!$params['score_win']) {
            $params['score_win'] = $score;
            $this->createdWinner($options, $promotionId, $params, $params['message_near_win']);
            return [
                'message' => $params['message_near_win'],
                'price_topup' => '',
            ];
        }

        $params['message_near_win'] = str_replace('{diem_thieu_de_trung}', ($params['score_win'] - $scoreRemain), $params['message_near_win']);
        // check if limit win != 0
        if ($params['limit_win'] > 0) {
            if (count($arrWinnerPromotions) >= $params['limit_win']) {
                return [
                    'message' => $params['message_limit_win'],
                    'price_topup' => '',
                ];
            }
        }
        $scores = explode($params['score_win'], ',');
        $scores = sort($scores);
        foreach ($scores as $item) {
            if ($scoreRemain < $item) {
                $params['message_near_win'] = str_replace('{diem_thieu_de_trung}', ($item - $scoreRemain), $params['message_near_win']);
                $data = ['score' => $winner['score'] + $score, 'updated_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()];
                $entityWinnerPromotions->updateRow($winner['id'], $data);
                return [
                    'message' => $params['message_near_win'],
                    'price_topup' => '',
                ];
            }
        }
        if ($scoreRemain >= $params['score_win']) {
            if (empty($winner)) {
                $message = $params['message_win'];
                $this->createdWinner($options, $promotionId, $params, $message);
                return [
                    'message' => $message,
                    'price_topup' => (isset($params['price_topup']) ? $params['price_topup'] : ''),
                ];
            }
            $data = ['score' => $winner['score'] + $score, 'message' => $params['message_win'], 'updated_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()];
            $entityWinnerPromotions->updateRow($winner['id'], $data);
        }
    }

    private function handleTypeRandom($promotionId = null, $options = null, $params = null)
    {
        if ($params == null) return false;
        // record data
        $entityListPromotions = new ListPromotions($this->adapter);
        $entityWinnerPromotions = new WinnerPromotions($this->adapter);
        $data = [
            'company_id' => $options['company_id'],
            'code_id' => $options['code_id'],
            'phone_id' => $options['phone_id'],
            'promotion_id' => $promotionId,
            'is_type' => self::IS_TYPE_RANDOM,
            'score' => 0,
            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
        ];
        $entityListPromotions->addRow($data);
        // check condition
        // get data winner promotions as phone_id and promotion_id, check if limit win != 0
        $arrWinnerPromotions = $entityWinnerPromotions->fetchAlls(['promotion_id' => $promotionId, 'phone_id' => $options['phone_id']]);
        if ($params['limit_win'] > 0) {
            if (count($arrWinnerPromotions) >= $params['limit_win']) {
                return [
                    'message' => $params['message_limit_win'],
                    'price_topup' => '',
                ];
            }
        }
        $isWin = 0;
        if ($params['is_random'] == 0) {
            // get total winner promotions as promotion_id
            $totalWinnerPromotions = $entityWinnerPromotions->fetchTotalAsPromotions($promotionId);
            $arrOrderWin = explode(',', $params['order_win']);
            if (in_array($totalWinnerPromotions, $arrOrderWin)) {
                $isWin = 1;
            }
        } else {
            $time = time();
            if ($params['is_random'] == 1) {
                $isWin = (($time % 2) == 0) ? 1 : 0;
            } else if ($params['is_random'] == 2) {
                $isWin = (($time % 4) == 0) ? 1 : 0;
            } else if ($params['is_random'] == 3) {
                $isWin = (($time % 16) == 0) ? 1 : 0;
            }
        }

        if ($isWin == 1) {
            $message = $params['message_win'];
            $dataWinnerPromotion = [
                'company_id' => $options['company_id'],
                'promotion_id' => $promotionId,
                'phone_id' => $options['phone_id'],
                'message' => $message,
                'score' => 0,
                'price_topup' => (isset($params['price_topup']) ? $params['price_topup'] : ''),
                'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
            ];
            $entityWinnerPromotions->addRow($dataWinnerPromotion);
            return [
                'message' => $message,
                'price_topup' => (isset($params['price_topup']) ? $params['price_topup'] : ''),
            ];
        } else {
            return [
                'message' => '',
                'price_topup' => '',
            ];
        }
    }

    public function createdWinner($options, $promotionId, $params, $message)
    {
        $entityWinnerPromotions = new WinnerPromotions($this->adapter);
        $dataWinnerPromotion = [
            'company_id' => $options['company_id'],
            'promotion_id' => $promotionId,
            'phone_id' => $options['phone_id'],
            'message' => $message,
            'score' => $params['score_win'],
            'price_topup' => (isset($params['price_topup']) ? $params['price_topup'] : ''),
            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
        ];
        $entityWinnerPromotions->addRow($dataWinnerPromotion);
    }

}