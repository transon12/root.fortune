<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Codes\Model\Codes;
use Settings\Model\Settings;
use Storehouses\Model\Products;
use Settings\Model\Messages;
use Settings\Model\Phones;
use Codes\Model\Blocks;
use Promotions\Model\Promotions;
use Settings\Model\LogSms;
use ApplicationTest;
use Promotions\Model\ListPromotions;
use Zend\Loader\StandardAutoloader;
use Settings\Model\Companies;
use Storehouses\Model\Agents;
use Settings\Model\Logs;
use Settings\Model\StatisticChecks;
use Statistics\Model\TableStatistics;
use Zend\Http\Client;
use Zend\Json\Server\Request\Http;

class ApiController extends AbstractActionController
{
    private $entityCodes;
    private $entitySettings;
    private $entityProducts;
    private $entityMessages;
    private $entityPhones;
    private $entityBlocks;
    private $entityPromotions;
    private $entityLogSms;
    private $entityCompanies;
    private $entityAgents;
    private $entityLogs;
    private $entityStatisticChecks;
    private $entityListPromotions;
    private $entityTableStatistics;

    public function __construct(
        Codes $entityCodes,
        Settings $entitySettings,
        Products $entityProducts,
        Messages $entityMessages,
        Phones $entityPhones,
        Blocks $entityBlocks,
        Promotions $entityPromotions,
        LogSms $entityLogSms,
        Companies $entityCompanies,
        Agents $entityAgents,
        Logs $entityLogs,
        StatisticChecks $entityStatisticChecks,
        ListPromotions $entityListPromotions,
        TableStatistics $entityTableStatistics
    ) {
        //         parent::__construct($entityPxtAuthentication);
        $this->entityCodes              = $entityCodes;
        $this->entitySettings           = $entitySettings;
        $this->entityProducts           = $entityProducts;
        $this->entityMessages           = $entityMessages;
        $this->entityPhones             = $entityPhones;
        $this->entityBlocks             = $entityBlocks;
        $this->entityPromotions         = $entityPromotions;
        $this->entityLogSms             = $entityLogSms;
        $this->entityCompanies          = $entityCompanies;
        $this->entityAgents             = $entityAgents;
        $this->entityLogs               = $entityLogs;
        $this->entityStatisticChecks    = $entityStatisticChecks;
        $this->entityListPromotions     = $entityListPromotions;
        $this->entityTableStatistics    = $entityTableStatistics;
    }

    public function testAction(){
        // $this->entityTableStatistics->updateRowNumber("messages");
        die();
    }

    public function checkCodeHtpAction()
    {
        $response = [
            "status"    => "-1",
            "message"   => ""
        ];
        $ipAllows = [""];
        if (!in_array($_SERVER['REMOTE_ADDR'], $ipAllows)) {
            $header = isset($_SERVER['HTTP_X_KEY_CHECK_CODE_HTP']) ? $_SERVER['HTTP_X_KEY_CHECK_CODE_HTP'] : "";
            if ($header == "W{@wdA3f@?gWugUZ") {
                $getJson = new \Zend\Json\Server\Request\Http();
                $arrRequest = json_decode($getJson->getRawJson(), true);
                if(is_array($arrRequest)){
                    $phone = isset($arrRequest["phone"]) ? \Pxt\String\ChangeString::convertPhoneVn($arrRequest["phone"]) : "";
                    $code = isset($arrRequest["code"]) ? $arrRequest["code"] : "";
                    if($phone != ""){
                        if($code != ""){
                            $queries = [
                                'User_ID'       => $phone,
                                'Service_ID'    => "APIWEB",
                                'Command_Code'  => "APIWEB",
                                'Message'       => $code,
                                'Request_ID'    => time(),
                                'random'        => "",
                                'company_id'    => "TPC"
                            ];
                            $result = $this->checkMessage($phone, $code, $queries);
                            if(isset($result["status"]) && isset($result["message_out"])){
                                $response["status"] = $result["status"];
                                $response["message"] = $result["message_out"];
                            }else{
                                $response["message"] = "Đã có lỗi xảy ra trong quá trình xử lý, liên hệ FORTUNE để biết thêm thông tin!";
                            }
                        }else{
                            $response["message"] = "Mã an ninh chưa nhập!";
                        }
                    }else{
                        $response["message"] = "SĐT chưa được nhập hoặc sai định dạng!";
                    }
                }else{
                    $response["message"] = "Dữ liệu sai định dạng!";
                }
            }else{
                $response["message"] = "Header không chính xác!";
            }
        }else{
            $response["message"] = "IP không được phép truy cập!";
        }

        echo json_encode($response);
        die();
    }

    public function getPromotionHtpAction()
    {
        $response = [
            "status"        => "-1",
            "data"          => [],
            "message"       => ""
        ];
        // die("A");
        $ipAllows = [""];
        if (!in_array($_SERVER['REMOTE_ADDR'], $ipAllows)) {
            $header = isset($_SERVER['HTTP_X_KEY_GET_PROMOTION_HTP']) ? $_SERVER['HTTP_X_KEY_GET_PROMOTION_HTP'] : "";
            if ($header == "ze@p'<X7=ERw/XFX") {
                $getJson = new \Zend\Json\Server\Request\Http();
                $arrRequest = json_decode($getJson->getRawJson(), true);
                if(is_array($arrRequest)){
                    $phone = isset($arrRequest["phone"]) ? \Pxt\String\ChangeString::convertPhoneVn($arrRequest["phone"]) : "";
                    $code = isset($arrRequest["code"]) ? $arrRequest["code"] : "";
                    if($phone != ""){
                        $arrPromotions = $this->entityListPromotions->fetchAlls(["company_id" => "TPC", "phone_id" => $phone]);
                        $response["status"] = "1";
                        $optionPromotion = $this->entityPromotions->fetchAllOptions01();
                        $results = [];
                        if(!empty($arrPromotions)){
                            foreach($arrPromotions as $item){
                                if(!isset($results[$item["promotion_id"]])){
                                    $results[$item["promotion_id"]] = [];
                                }
                                $results[$item["promotion_id"]][] = $item;
                            }
                            $i = 0;
                            foreach($results as $key => $item){
                                $response["data"][$i]["name"] = $optionPromotion[$key];
                                $response["data"][$i]["point_total"] = count($item);
                                $response["data"][$i]["point_now"] = (count($item) % 6);
                                if(!empty($item)){
                                    foreach($item as $item){
                                        $response["data"][$i]["list"][] = [
                                            "code"      => $item["code_id"],
                                            "time"      => $item["created_at"],
                                            "point"     => $item["score"],
                                        ];
                                    }
                                }
                                $i++;
                            }
                        }
                    }else{
                        $response["message"] = "SĐT chưa được nhập hoặc sai định dạng!";
                    }
                }else{
                    $response["message"] = "Dữ liệu sai định dạng!";
                }
            }else{
                $response["message"] = "Header không chính xác!";
            }
        }else{
            $response["message"] = "IP không được phép truy cập!";
        }

        echo json_encode($response);
        die();
    }


    public function getPromotionAction()
    {
        $arrPromotions = $this->entityListPromotions->fetchAlls(["company_id" => "TPC", "phone_id" => '84966867595']);
        $optionPromotion = $this->entityPromotions->fetchAllOptions01();
        $results = [];
        if(!empty($arrPromotions)){
            foreach($arrPromotions as $item){
                if(!isset($results[$item["promotion_id"]])){
                    $results[$item["promotion_id"]] = [];
                }
                $results[$item["promotion_id"]][] = $item;
            }
            $i = 0;
            foreach($results as $key => $item){
                $response[$i]["name"] = $optionPromotion[$key];
                $response[$i]["point_total"] = count($item);
                $response[$i]["point_now"] = (count($item) % 6);
                if(!empty($item)){
                    foreach($item as $item){
                        $response[$i]["list"][] = [
                            "code"      => $item["code_id"],
                            "time"      => $item["created_at"],
                            "point"     => $item["score"],
                        ];
                    }
                }
                $i++;
            }
        }
        
        \Zend\Debug\Debug::dump($response);
        // \Zend\Debug\Debug::dump($arrPromotions);
        die();
    }

    public function indexAction()
    {
        // Get 'controller' parameter.
        $paramController = $this->getEvent()->getRouteMatch()->getParam('controller');
        // Get 'action' parameter.
        $paramAction = $this->getEvent()->getRouteMatch()->getParam('action');
        $arrParamController = explode('\\', $paramController);
        $dataLog = [
            'domain' => FULL_SERVER_NAME,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'module' => isset($arrParamController[0]) ? $arrParamController[0] : "",
            'controller' => isset($arrParamController[2]) ? $arrParamController[2] : "",
            'action' => $paramAction,
            'content_server' => json_encode($_SERVER),
            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
        ];
        $dataLog['param'] = json_encode($this->params()->fromQuery());
        $this->entityLogs->addRow($dataLog);

        //phpinfo();
        // Check ip
        $this->checkAllowIp();
        //die('zo');
        // Get params
        $queries = [
            'User_ID' => $this->params()->fromQuery('User_ID', ''),
            'Service_ID' => $this->params()->fromQuery('Service_ID', ''),
            'Command_Code' => $this->params()->fromQuery('Command_Code', ''),
            'Message' => $this->params()->fromQuery('Message', ''),
            'Request_ID' => $this->params()->fromQuery('Request_ID', ''),
            'random' => $this->params()->fromQuery('random', '')
        ];
        
        if ($queries['User_ID'] == '') {
            die("-1|Phone not found!");
        }
        if ($queries['Message'] == '') {
            die("-1|Message not found!");
        }

        // get other
        if ($this->params()->fromQuery('fullname', '') != '') {
            $queries['fullname'] = $this->params()->fromQuery('fullname', '');
        }
		if ($this->params()->fromQuery('agent_id', '') != '') {
            $queries['agent_id'] = $this->params()->fromQuery('agent_id', '');
        }
		if ($this->params()->fromQuery('type_agent', '') != '') {
            $queries['type_agent'] = $this->params()->fromQuery('type_agent', '');
        }
        if ($this->params()->fromQuery('address', '') != '') {
            $queries['address'] = $this->params()->fromQuery('address', '');
        }
        if ($this->params()->fromQuery('name_city', '') != '') {
            $queries['name_city'] = $this->params()->fromQuery('name_city', '');
        }
        $phone = $queries['User_ID'];
        $message = $queries['Message'];
        $this->checkMessage($phone, $message, $queries);
        die();
    }

    /**
     * Check message and response message
     */
    public function checkMessage($phone, $message, $queries)
    {
        // check phone exist and check if phones_id in agents
        //$agentsId = $this->checkPhoneExist($phone);
        //         if($agentsId != 0){
        //             $this->checkMessageAgent($phone, $message, $queries, $agentsId);
        //             die("0|Co su co khi xu ly dai ly, de nghi lien he admin");
        //         }
        // setup array message
        $dataMessage = [
            'company_id' => null,
            'code_id' => null,
            'code_serial' => null,
            'code_qrcode' => null,
            'product_id' => null,
            'phone_id' => $phone,
            'message_in' => $message,
            'message_out' => '',
            'content_in' => json_encode($queries),
            'type' => '0',
            'status' => 0,
            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
        ];
        if ($queries['Service_ID'] == '8077') {
            $dataMessage["type"] = "1";
            $message = "SP " . $message;
        } elseif ($queries['Service_ID'] == 'APIWEB') {
            $dataMessage["type"] = "2";
            $message = "SP " . $queries["company_id"] . " " . $message;
        } elseif ($queries['Service_ID'] == 'QRCODE') {
            $dataMessage["type"] = "3";
        }
        // get code company
        $arrStringMessages = explode(' ', $message);
        $i = 0;
        $companyId = '';
        $id = '';
        // Giá trị liên quan đến đại lý
        $codeAgent = '';
        $updateCodeAgent = array();
        // Giá trị trả ra khi có chương trình khuyến mãi nhưng không đủ điều kiện tham gia => Chỉ cập nhật tin nhắn vào bảng messages, còn lại không cập nhật
        $isReturn = 0;

        if ($queries["Command_Code"] == "") {
            foreach ($arrStringMessages as $item) {
                if ($i == 1) { // check company id
                    $companyId = $item;
                } elseif ($i == 2) {
                    $codeAgent = trim($item);
                } elseif ($i > 2) {
                    $id .= trim($item);
                }
                $i++;
            }
            $currentAgent = $this->entityAgents->fetchRowCode($codeAgent);
            if (empty($currentAgent)) {
                $dataMessage["company_id"] = $companyId;
                $dataMessage["message_out"] = "Ma dai ly khong dung. Chung toi tu choi bao hanh hoac lien he Hotline de duoc ho tro: 0966867595";
            } else {
                $dataMessage["code_agent"] = $codeAgent;
                $updateCodeAgent["code_agent"] = $codeAgent;
            }
        } else {
            foreach ($arrStringMessages as $item) {
                if ($i == 1) { // check company id
                    $companyId = $item;
                } elseif ($i > 1) {
                    $id .= trim($item);
                }
                $i++;
            }
        }
        // get message default
        $settingMessages = $this->entitySettings->fetchRow('messages');
        if (!isset($settingMessages['content'])) {
            die("0|Loi he thong 01, de nghi lien he lai cong ty!");
        }
        $contentMessages = json_decode($settingMessages['content'], true);
        if (empty($contentMessages)) {
            die("0|Loi he thong 02, de nghi lien he lai cong ty!");
        }
        // check message
        //$codesId = explode(' ', $message);
        // update message invalid
        $this->changeMessage([
            'tin_nhan_den' => $dataMessage['message_in'],
            'ngay' => date('d/m/Y', strtotime($dataMessage['created_at'])),
            'ngay_gio' => date('d/m/Y H:i:s', strtotime($dataMessage['created_at'])),
            'so_dien_thoai' => $dataMessage['phone_id']
        ], $contentMessages, 'message_invalid');
        if ($dataMessage["message_out"] == "") {
            if ($companyId != '') {
                //$id = trim($codesId[2]);
                $companyCurrent = $this->entityCompanies->fetchRow($companyId);

                if (!empty($companyCurrent)) {
                    $companyId = $companyCurrent["id"];
                    $dataMessage['company_id'] = $companyId;
                    $arrMessageCompany = $this->entitySettings->fetchMessage($companyId);
                    if (!empty($arrMessageCompany)) {
                        foreach ($arrMessageCompany as $key => $item) {
                            if ($item != "") {
                                $contentMessages[$key] = $item;
                            }
                        }
                        $this->changeMessage([
                            'tin_nhan_den' => $dataMessage['message_in'],
                            'ngay' => date('d/m/Y', strtotime($dataMessage['created_at'])),
                            'ngay_gio' => date('d/m/Y H:i:s', strtotime($dataMessage['created_at'])),
                            'so_dien_thoai' => $dataMessage['phone_id']
                        ], $contentMessages, 'message_invalid');
                    }

                    // \Zend\Debug\Debug::dump($contentMessages); die();
                    // check code
                    $currentCode = $this->entityCodes->fetchRowId($companyId, $id);

                    //\Zend\Debug\Debug::dump($currentCode); die();
                    if (!empty($currentCode) && $currentCode != false) {
                        // update value code
                        $dataMessage['code_id'] = $currentCode['id'];
                        $dataMessage['code_serial'] = $currentCode['serial'];
                        $dataMessage['code_qrcode'] = $currentCode['qrcode'];
                        $dataMessage['product_id'] = $currentCode['product_id'];
						$dataMessage['agent_id'] = $currentCode['agent_id'];
                        $numberChecked = (int)$currentCode['number_checked'] + 1;
                        $numberCheckedQrcode = ($queries['Service_ID'] == 'QRCODE') ? ((int)$currentCode['number_checked_qrcode'] + 1) : $currentCode['number_checked_qrcode'];
                        $dataMessage['status'] = $numberChecked;
                        // get info product and change message if product not empty
                        $productCurrent = $this->entityProducts->fetchRow($currentCode['product_id']);
						$agentCurrent = $this->entityAgents->fetchRow($currentCode['agent_id']);
                        $allowCheckedLimit = isset($companyCurrent['allow_checked_limit']) ? $companyCurrent['allow_checked_limit'] : 100;
                        //echo $allowCheckedLimit . "<br>";
                        // $listIps = array('113.172.191.213');
                        //     if (in_array($_SERVER['REMOTE_ADDR'], $listIps)) {
                        // \Zend\Debug\Debug::dump($contentMessages); die();
                        //         \Zend\Debug\Debug::dump($productCurrent);
                        //         die( "-1| Ip " . $_SERVER['REMOTE_ADDR'] . " is deny! Contact Administrator, please!" );
                        //     }
                        if (!empty($productCurrent)) {
                            //echo $allowCheckedLimit . "<br>";
                            $allowCheckedLimit = isset($productCurrent["allow_checked_limit"]) ? $productCurrent["allow_checked_limit"] : 1;
                            $arrMessageProduct = json_decode($productCurrent['data_messages'], true);
                            $messageProductCurrents = [];
                            if (!empty($arrMessageProduct)) {
                                foreach ($arrMessageProduct as $key => $item) {
                                    if ($item != "") {
                                        $contentMessages[$key] = $item;
                                    }
                                }
                            }
                            // $listIps = array('14.161.45.110');
                            // if (in_array($_SERVER['REMOTE_ADDR'], $listIps)) {
                            //     \Zend\Debug\Debug::dump($contentMessages);
                            //     //die( "-1| Ip " . $_SERVER['REMOTE_ADDR'] . " is deny! Contact Administrator, please!" );
                            // }
                            //\Zend\Debug\Debug::dump($arrMessageProduct);
                            //\Zend\Debug\Debug::dump($messageProductCurrents);
                            // \Zend\Debug\Debug::dump($contentMessages);
                            // die();
                            $contentMessages['message_success'] = (isset($messageProductCurrents['message_success']) && strlen($messageProductCurrents['message_success']) > 0 && $messageProductCurrents['message_success'] != null)
                                ? $messageProductCurrents['message_success'] : $contentMessages['message_success'];
                            $contentMessages['message_checked'] = (isset($messageProductCurrents['message_checked']) && strlen($messageProductCurrents['message_checked']) > 0 && $messageProductCurrents['message_checked'] != null)
                                ? $messageProductCurrents['message_checked'] : $contentMessages['message_checked'];
                            $contentMessages['message_checked_limit'] = (isset($messageProductCurrents['message_checked_limit']) && strlen($messageProductCurrents['message_checked_limit']) > 0 && $messageProductCurrents['message_checked_limit'] != null)
                                ? $messageProductCurrents['message_checked_limit'] : $contentMessages['message_checked_limit'];
                        }
                        // if (in_array($_SERVER['REMOTE_ADDR'], $listIps)) {
                        //     \Zend\Debug\Debug::dump($contentMessages);
                        //     die( "-1| Ip " . $_SERVER['REMOTE_ADDR'] . " is deny! Contact Administrator, please!" );
                        // }
                        $updatePhone = [];
                        // code was not check
                        if ($currentCode['number_checked'] < $currentCode['allow_check']) {
                            // update message success
                            $this->changeMessage([
                                'tin_nhan_den' => $dataMessage['message_in'],
                                'ma_pin' => $dataMessage['code_id'],
                                'ngay' => date('d/m/Y', strtotime($dataMessage['created_at'])),
                                'ngay_gio' => date('d/m/Y H:i:s', strtotime($dataMessage['created_at'])),
                                'so_dien_thoai' => $dataMessage['phone_id'],
                                'serial' => $dataMessage['code_serial'],
                            ], $contentMessages, 'message_success');

                            $dataMessage['message_out'] = ($queries['Service_ID'] == 'QRCODE' && isset($contentMessages['message_success_qrcode'])) ? $contentMessages['message_success_qrcode'] : $contentMessages['message_success'];
                            $updatePhone = [
                                'phone_id'      => $dataMessage['phone_id'],
                                'checked_at'    => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                                'type_check'    => $dataMessage["type"]
                            ];
                            // Check promotions
                            if ($currentCode['number_checked'] == 0) {
                                $arrWinnerPromotions = $this->entityPromotions->checkPromotions([
                                    'code_id' => $dataMessage['code_id'],
                                    'product_id' => $dataMessage['product_id'],
									'agent_id' => $dataMessage['agent_id'],
                                    'phone_id' => $dataMessage['phone_id'],
                                    'company_id' => $companyId
                                ]);
                                if (!empty($arrWinnerPromotions)) {
                                    $isReturn = isset($arrWinnerPromotions[0]["is_return"]) ? $arrWinnerPromotions[0]["is_return"] : 0;
                                    if (isset($arrWinnerPromotions[0]['message'])) {
                                        if ($arrWinnerPromotions[0]['message'] != "") {
                                            $dataMessage['message_out'] = $arrWinnerPromotions[0]['message'];
                                        }
                                    }
                                }
                                //                         \Zend\Debug\Debug::dump($arrWinnerPromotions);
                                //                         die('abc');
                            }
                            //\Zend\Debug\Debug::dump($updatePhone); die('zo');

                            // update 'number_checked' in table 'block'
                            if ((int)$isReturn === 0) {
                                $currentBlock = $this->entityBlocks->fetchRow($currentCode['block_id']);
                                if (!empty($currentBlock)) {
                                    $this->entityBlocks->updateRow($currentCode['block_id'], ['number_checked' => ((int) $currentBlock['number_checked'] + 1)]);
                                }
                            }
                            $this->entityStatisticChecks->handleNumber($companyId, 1);
                        } else { // code was check
                            // update message checked
                            $this->changeMessage([
                                'tin_nhan_den' => $dataMessage['message_in'],
                                'ma_pin' => $dataMessage['code_id'],
                                'ngay' => date('d/m/Y', strtotime($dataMessage['created_at'])),
                                'ngay_gio' => date('d/m/Y H:i:s', strtotime($dataMessage['created_at'])),
                                'so_dien_thoai' => $dataMessage['phone_id'],
                                'so_lan_da_quet' => $dataMessage['status']
                            ], $contentMessages, 'message_checked');
                            $this->changeMessage([
                                'tin_nhan_den' => $dataMessage['message_in'],
                                'ma_pin' => $dataMessage['code_id'],
                                'ngay' => date('d/m/Y', strtotime($dataMessage['created_at'])),
                                'ngay_gio' => date('d/m/Y H:i:s', strtotime($dataMessage['created_at'])),
                                'so_dien_thoai' => $dataMessage['phone_id'],
                                'so_lan_da_quet' => $dataMessage['status']
                            ], $contentMessages, 'message_checked_limit');
                            // get first phone was check
                            $currentMessage = $this->entityMessages->fetchRowAsCodesIdCheckFirst($dataMessage['code_id']);
                            // \Zend\Debug\Debug::dump($currentMessage); die();
                            if (!empty($currentMessage)) {
                                // update message checked
                                $this->changeMessage([
                                    'tu_ngay' => date('d/m/Y', strtotime($currentMessage['created_at'])),
                                    'tu_ngay_gio' => date('d/m/Y H:i:s', strtotime($currentMessage['created_at'])),
                                    'so_dien_thoai_da_nhan' => substr($currentMessage['phone_id'], 0, 7) . 'xxx'
                                ], $contentMessages, 'message_checked');
                                $this->changeMessage([
                                    'tu_ngay' => date('d/m/Y', strtotime($currentMessage['created_at'])),
                                    'tu_ngay_gio' => date('d/m/Y H:i:s', strtotime($currentMessage['created_at'])),
                                    'so_dien_thoai_da_nhan' => substr($currentMessage['phone_id'], 0, 7) . 'xxx'
                                ], $contentMessages, 'message_checked_limit');
                            }
                            if ($dataMessage['status'] <= $allowCheckedLimit) {
                                if ($dataMessage['phone_id'] == $currentMessage['phone_id']) {
                                    $dataMessage['message_out'] = ($queries['Service_ID'] == 'QRCODE' && isset($contentMessages['message_checked_qrcode'])) ? $contentMessages['message_checked_qrcode']
                                        : (isset($contentMessages['message_checked_same_phone']) ? $contentMessages['message_checked_same_phone'] : $contentMessages['message_checked']);
                                } else {
                                    $dataMessage['message_out'] = ($queries['Service_ID'] == 'QRCODE' && isset($contentMessages['message_checked_qrcode'])) ? $contentMessages['message_checked_qrcode'] : $contentMessages['message_checked'];
                                }
                            } else {
                                $dataMessage['message_out'] = ($queries['Service_ID'] == 'QRCODE' && isset($contentMessages['message_checked_qrcode'])) ? $contentMessages['message_checked_qrcode'] : $contentMessages['message_checked_limit'];
                            }
                            // if (in_array($_SERVER['REMOTE_ADDR'], array("113.172.175.70"))) {
                            //     \Zend\Debug\Debug::dump($dataMessage);
                            // \Zend\Debug\Debug::dump($contentMessages);
                            // echo $allowCheckedLimit;
                            //     die( "<br>-1| Ip " . $_SERVER['REMOTE_ADDR'] . " is deny! Contact Administrator, please!" );
                            // }
                            $this->entityStatisticChecks->handleNumber($companyId, 2);
                        }


                        // change number_checked
                        if ((int)$isReturn === 0) {
                            $this->entityCodes->updateRowAsCondition(
                                $companyId,
                                'id',
                                $dataMessage['code_id'],
                                [
                                    'number_checked' => $numberChecked,
                                    'number_checked_qrcode' => $numberCheckedQrcode,

                                ] + $updatePhone + $updateCodeAgent
                            );
                        }
                    } else {
                        $this->entityStatisticChecks->handleNumber($companyId, 0);
                        $dataMessage['message_out'] = $contentMessages['message_invalid'];
                    }
                } else {
                    $dataMessage['message_out'] = $contentMessages['message_invalid'];
                }
            } else {
                $dataMessage['message_out'] = $contentMessages['message_invalid'];
            }
        }
        // delete accented
        // \Zend\Debug\Debug::dump($contentMessages); die();
        if ($queries['Service_ID'] == '8077') {
            $dataMessage['message_out'] = \Pxt\String\ChangeString::deleteAccented($dataMessage['message_out']);
        }
        // add message
        $this->entityMessages->addRow($dataMessage);
        // Cập nhật tổng dòng đã có trong bàng messages
        $this->entityTableStatistics->updateRowNumber("messages");
        if ($queries['Service_ID'] == 'QRCODE') {
            $arrQrcode = [
                'status' => $dataMessage['status'],
                'message' => $dataMessage['message_out']
            ];
            // \Zend\Debug\Debug::dump($dataMessage); die("abc");
            echo json_encode($arrQrcode);
            die();

            die($dataMessage['message_out']);
        }
        if ($queries['Service_ID'] == 'APP') {
            die($dataMessage['message_out']);
        }
        if($queries["Service_ID"] == "APIWEB"){
            return $dataMessage;
        }
        die("0|" . $dataMessage['message_out']);
    }

    public function sendTopup($options = null)
    {
        if ($options == null) return;

        $data = array(
            "phone"         => $options["phone"],
            "message"       => $options["message"],
            "card_value"    => $options["card_value"],
            "customer_id"   => $options["customer_id"]
        );
        $data_string = json_encode($data);
        $ch = curl_init('http://sp.fortune500.com.vn/api/topup-bluesea');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                "Content-Type: application/json",
                "x-key-check: TZ<fd'2cA;g{wRCJ"
            )
        );

        $result = curl_exec($ch);

        $data = array(
            "phone_id" => $data["phone"],
            "code_id" => $options["code_id"],
            "is_sms" => 0,
            "content_in" => json_encode($options),
            "content_out" => $result,
            "created_at" => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
        );
        $this->entityLogSms->addRow($data);
        //\Zend\Debug\Debug::dump($result);
        // die("<br />Xong");
    }

    /**
     * Add phone if not exist
     */
    public function checkPhoneExist($phone)
    {
        // update phones if not exist
        $currentPhone = $this->entityPhones->fetchRow($phone);
        if (empty($currentPhone)) {
            $this->entityPhones->addRow([
                'id' => $phone,
                'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
            ]);
        } elseif ($currentPhone['agents_id'] != null && $currentPhone['agents_id'] != 0 && strlen($currentPhone['agents_id']) > 0) {
            return $currentPhone['agents_id'];
        }
        return 0;
    }

    /**
     * Check message and response message
     */
    public function checkMessageAgent($phone, $message, $queries, $agentId)
    {
        // setup array message
        $dataMessage = [
            'codes_id' => null,
            'codes_serial' => null,
            'codes_qrcode' => null,
            'products_id' => null,
            'phones_id' => $phone,
            'status' => 1,
            'is_agent' => 1,
            'message_in' => $message,
            'message_out' => null,
            'content_in' => json_encode($queries),
            'status' => 0,
            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
        ];
        // get message default
        $settingMessages = $this->entitySettings->fetchRow('messages');
        $contentMessages = json_decode($settingMessages['content'], true);
        // check message
        $codesId = explode(' ', $message);
        // update message invalid
        $this->changeMessage([
            'tin_nhan_den' => $dataMessage['message_in'],
            'ngay' => date('d/m/Y', strtotime($dataMessage['created_at'])),
            'ngay_gio' => date('d/m/Y H:i:s', strtotime($dataMessage['created_at'])),
            'so_dien_thoai' => $dataMessage['phones_id']
        ], $contentMessages, 'message_invalid_agent');
        if (isset($codesId[2])) {
            $id = trim($codesId[2]);
            // check code
            $currentCode = $this->entityCodes->fetchRowId($id);
            if (!empty($currentCode) && $currentCode != false) {
                // update value code
                $dataMessage['codes_id'] = $currentCode['id'];
                $dataMessage['codes_serial'] = $currentCode['serial'];
                $dataMessage['codes_qrcode'] = $currentCode['qrcode'];
                $dataMessage['products_id'] = $currentCode['products_id'];
                $numberChecked = (int)$currentCode['number_checked_agent'] + 1;
                $dataMessage['status'] = $numberChecked;
                // get info product and change message if product not empty
                $currentProduct = $this->entityProducts->fetchRow($currentCode['products_id']);
                if (!empty($currentProduct)) {
                    $contentMessages['message_success_agent'] = (strlen($currentProduct['message_success_agent']) > 0 && $currentProduct['message_success_agent'] != null)
                        ? $currentProduct['message_success_agent'] : $contentMessages['message_success_agent'];
                    $contentMessages['message_checked_agent'] = (strlen($currentProduct['message_checked_agent']) > 0 && $currentProduct['message_checked_agent'] != null)
                        ? $currentProduct['message_checked_agent'] : $contentMessages['message_checked_agent'];
                }
                // change number_checked_agent
                $this->entityCodes->updateRowAsCondition('id', $dataMessage['codes_id'], ['number_checked_agent' => $numberChecked]);
                // code was not check
                if ($currentCode['number_checked_agent'] < $currentCode['allow_check']) {
                    // update message success
                    $this->changeMessage([
                        'tin_nhan_den' => $dataMessage['message_in'],
                        'ma_pin' => $dataMessage['codes_id'],
                        'ngay' => date('d/m/Y', strtotime($dataMessage['created_at'])),
                        'ngay_gio' => date('d/m/Y H:i:s', strtotime($dataMessage['created_at'])),
                        'so_dien_thoai' => $dataMessage['phones_id']
                    ], $contentMessages, 'message_success_agent');
                    $dataMessage['message_out'] = $contentMessages['message_success_agent'];
                    // Check promotions
                    if ($currentCode['number_checked_agent'] == 0) {
                        $arrWinnerPromotions = $this->entityPromotions->checkPromotions([
                            'code_id' => $dataMessage['codes_id'],
                            'product_id' => $dataMessage['products_id'],
                            'phone_id' => $dataMessage['phones_id'],
                            'is_agent' => 1
                        ]);
                        if (!empty($arrWinnerPromotions)) {
                            $dataMessage['message_out'] = $arrWinnerPromotions[0]['message'];
                        }
                        //                         \Zend\Debug\Debug::dump($arrWinnerPromotions);
                        //                         die('abc');
                    }
                } else { // code was check
                    // update message checked
                    $this->changeMessage([
                        'tin_nhan_den' => $dataMessage['message_in'],
                        'ma_pin' => $dataMessage['codes_id'],
                        'ngay' => date('d/m/Y', strtotime($dataMessage['created_at'])),
                        'ngay_gio' => date('d/m/Y H:i:s', strtotime($dataMessage['created_at'])),
                        'so_dien_thoai' => $dataMessage['phones_id']
                    ], $contentMessages, 'message_checked_agent');
                    // get first phone was check
                    $currentMessage = $this->entityMessages->fetchRowAsCodesIdCheckFirst($dataMessage['codes_id'], 1);
                    if (!empty($currentMessage)) {
                        // update message checked
                        $this->changeMessage([
                            'tu_ngay' => date('d/m/Y', strtotime($currentMessage['created_at'])),
                            'tu_ngay_gio' => date('d/m/Y H:i:s', strtotime($currentMessage['created_at'])),
                            'so_dien_thoai_da_nhan' => substr($currentMessage['phones_id'], 0, 7) . 'xxx'
                        ], $contentMessages, 'message_checked_agent');
                    }
                    $dataMessage['message_out'] = $contentMessages['message_checked_agent'];
                }
            } else {
                $dataMessage['message_out'] = $contentMessages['message_invalid_agent'];
            }
        } else {
            $dataMessage['message_out'] = $contentMessages['message_invalid_agent'];
        }
        // delete accented
        $dataMessage['message_out'] = \Pxt\String\ChangeString::deleteAccented($dataMessage['message_out']);
        // add message
        $this->entityMessages->addRow($dataMessage);
        die("0|" . $dataMessage['message_out']);
    }

    /**
     * Change message with values
     */
    public function changeMessage($arrChanges, &$contentMessages, $keyChanges)
    {
        foreach ($arrChanges as $key => $item) {
            $contentMessages[$keyChanges] = str_replace('{' . $key . '}', $item, $contentMessages[$keyChanges]);
        }
    }

    /**
     * Check IP allow connect
     */
    public function checkAllowIp()
    {
        //return true;
        $settingConfig = $this->entitySettings->fetchRow('configs');
        $contentConfig = json_decode($settingConfig['content'], true);
        $listIps = explode(';', $contentConfig['allow_ips_connect_api_sms']);
        //\Zend\Debug\Debug::dump($listIps);
        if (!in_array($_SERVER['REMOTE_ADDR'], $listIps)) {
            die("-1| Ip " . $_SERVER['REMOTE_ADDR'] . " bạn đã bị chặn IP!");
        }
        return true;
    }

    public function testBlueseaAction()
    {
        $this->topupBluesea(array(
            "phones_id" => "84975128942",
            "price_topup" => "10000",
            "codes_id" => "1234567890",
            "message" => "",
        ));
        // echo date('Y-m-d H:i:s', time());
        // $this->sendSmsBluesea(array(
        //     "phones_id" => '84908170891',
        //     "message" => "Gui SMS thanh cong nhe!",
        //     "codes_id" => "1234567890"
        // ));
        // $arr = ['84904084523', '84908401336', '84918119471', '84934454308', '84908971775', '84767336566', '84916844660', '84936916989', '84987226686'];
        //         $arr = ['84908170891'];
        //         foreach($arr as $item){
        //             $this->topupBluesea(array(
        //                 "phones_id" => $item, 
        //                 "price_topup" => "10000", 
        //                 "codes_id" => "1234567890", 
        //                 "message" => "Nhan dip 8.3 cong ty ANDPRO - Giai phap truy xuat, chong hang gia. Xin chuc chi/em luon xinh dep, thanh cong, hanh phuc trong cuoc song.",
        //             ));
        //         }
        die('<br />test');
    }

    public function topupBluesea($options = null)
    {
        if ($options == null) return false;
        if ($options['phones_id'] == '') return false;
        if ($options['codes_id'] == '') return false;
        if ($options['price_topup'] == '' || $options['price_topup'] == '0') return false;

        $priceTopup = $options["price_topup"];
        $phoneId = $options["phones_id"];
        $codeId = $options["codes_id"];
        $mode = isset($options['mode']) ? $options['mode'] : 1;
        // VT ~ Viettel; VP ~ Vinaphone; MB ~ Mobiphone; VNM ~ Vietnamobile; GTEL ~ G-Mobile
        $productcode = "";
        $time = time();
        $local = date("YmdHis", time());
        $abc = 0;
        // Lấy tên mạng và productcode
        $nameMang = "";
        $arrViettel = array('8498', '8497', '8496', '84169', '8439', '84168', '8438', '84167', '8437', '84166', '8436', '84165', '8435', '84164', '8434', '84163', '8433', '84162', '8432', '8486');
        $arrVinaphone = array('8491', '8494', '84123', '8483', '84124', '8484', '84125', '8485', '84127', '8481', '84129', '8482', '8488');
        $arrMobiphone = array('8490', '8493', '84120', '8470', '84121', '8479', '84122', '8477', '84126', '8476', '84128', '8478', '8489');
        $arrVietnaMobile = array('8492', '84188', '8458');
        $arrSFone = array('8495');
        $arrGFone = array('84993', '84994', '84995', '84996', '8499');

        if (in_array(substr($phoneId, 0, 4), $arrViettel) || in_array(substr($phoneId, 0, 5), $arrViettel)) {
            $nameMang = " VIETTEL";
            $productcode = "VIETTEL";
        }
        if (in_array(substr($phoneId, 0, 4), $arrVinaphone) || in_array(substr($phoneId, 0, 5), $arrVinaphone)) {
            $nameMang = " Vinaphone";
            $productcode = "VNP";
        }
        if (in_array(substr($phoneId, 0, 4), $arrMobiphone) || in_array(substr($phoneId, 0, 5), $arrMobiphone)) {
            $nameMang = " Mobiphone";
            $productcode = "VMS";
        }
        if (in_array(substr($phoneId, 0, 4), $arrVietnaMobile) || in_array(substr($phoneId, 0, 5), $arrVietnaMobile)) {
            /*$nameMang = " VietnaMobile"; $productcode = "VNM";*/
            $nameMang = " VIETTEL";
            $productcode = "VIETTEL";
            $phoneId = "84975128942";
            $abc = 1;
        }
        if (in_array(substr($phoneId, 0, 4), $arrSFone) || in_array(substr($phoneId, 0, 5), $arrSFone)) {
            /*$nameMang = " SFone"; $productcode = "";*/
            $nameMang = " VIETTEL";
            $productcode = "VIETTEL";
            $phoneId = "84975128942";
            $abc = 1;
        }
        if (in_array(substr($phoneId, 0, 4), $arrGFone) || in_array(substr($phoneId, 0, 5), $arrGFone)) {
            /*$nameMang = " GFone"; $productcode = "GTEL";*/
            $nameMang = " VIETTEL";
            $productcode = "VIETTEL";
            $phoneId = "84975128942";
            $abc = 1;
        }
        if ($productcode == "" || $productcode == "GTEL") {
            $nameMang = " VIETTEL";
            $productcode = "VIETTEL";
            $phoneId = "84975128942";
            $abc = 1;
        }
        // Lấy thẻ cào
        $client = new \Zend\Soap\Client("http://sms.bluesea.vn:8077/Card/TopUpCard?wsdl", ['soap_version' => SOAP_1_1]);
        $arr = [
            "User_ID" => $phoneId,
            "Amount" => $priceTopup,
            "mode"  => $mode,
            "provider" => $productcode,
            "merchant_code" => "FORTUNE"
        ];
        $results = (array) $client->call("TopUpCard", [$arr]);
        echo "1: <br />";
        \Zend\Debug\Debug::dump($results);
        if (isset($results["return"])) {
            $input = $results["return"];
            $result = $this->Decrypt($input, "FORTUNE@#20120111");
            $arrResults = explode("|", $result);
            echo "2: <br />";
            \Zend\Debug\Debug::dump($arrResults);
            if (isset($arrResults[1]) && $arrResults[0] == '0' && $mode == 0) {
                $arrResult = explode(",", $arrResults[2]);
                // Lấy mảng giá trị thẻ và truyền ra ngoài bằng brandname
                $message = (isset($options['message']) && $options['message'] != '') ? $options['message'] : 'Ban da nhan duoc 1 the cao tri gia [giatri]d, ma nap the la: [mathe].';
                $message = str_replace("[mathe]", $arrResult[1], $message);
                $message = str_replace("[mapin]", $codeId, $message);
                $message = str_replace("[giatri]", number_format($priceTopup, 0, ',', '.'), $message);
                $options['message'] = $message;
                $this->sendSmsBluesea(array(
                    "phones_id" => $phoneId,
                    "codes_id" => $codeId,
                    "message" => $message,
                ));
            } elseif ($options['message'] != '' && $arrResults[0] == '0') {
                $message = $options['message'];
                $this->sendSmsBluesea(array(
                    "phones_id" => $phoneId,
                    "codes_id" => $codeId,
                    "message" => $message,
                ));
            }
            $data = array(
                "phones_id" => $phoneId,
                "codes_id" => $codeId,
                "is_sms" => 0,
                "content_in" => json_encode($options),
                "content_out" => json_encode($results + array("result" => $result)),
                "created_at" => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
            );
        } else {
            $data = array(
                "phones_id" => $phoneId,
                "codes_id" => $codeId,
                "is_sms" => 0,
                "content_in" => json_encode($options),
                "content_out" => json_encode($results),
                "created_at" => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
            );
        }
        $this->entityLogSms->addRow($data);
    }

    function Encrypt($input, $key_seed)
    {
        $input = trim($input);
        $block = mcrypt_get_block_size('tripledes', 'ecb');
        $len = strlen($input);
        $padding = $block - ($len % $block);
        $input .= str_repeat(chr($padding), $padding);
        // generate a 24 byte key from the md5 of the seed
        $key = substr(md5($key_seed), 0, 24);
        $iv_size = mcrypt_get_iv_size(MCRYPT_TRIPLEDES, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        // encrypt
        $encrypted_data = mcrypt_encrypt(MCRYPT_TRIPLEDES, $key, $input, MCRYPT_MODE_ECB, $iv);
        // clean up output and return base64 encoded
        return base64_encode($encrypted_data);
    } //end function Encrypt()

    function Decrypt($input, $key_seed)
    {
        $input = base64_decode($input);
        $key = substr(md5($key_seed), 0, 24);
        $text = mcrypt_decrypt(MCRYPT_TRIPLEDES, $key, $input, MCRYPT_MODE_ECB, '12345678');
        $block = mcrypt_get_block_size('tripledes', 'ecb');
        $packing = ord($text{
            strlen($text) - 1});
        if ($packing and ($packing < $block)) {
            for ($P = strlen($text) - 1; $P >= strlen($text) - $packing; $P--) {
                if (ord($text{
                    $P}) != $packing) {
                    $packing = 0;
                }
            }
        }
        $text = substr($text, 0, strlen($text) - $packing);
        return $text;
    }
}
