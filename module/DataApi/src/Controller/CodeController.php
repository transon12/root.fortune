<?php

namespace DataApi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Admin\Model\PxtAuthentication;
use Codes\Model\Codes;
use DataApi\Core\DataApiCore;
use Settings\Model\StatisticChecks;
use Storehouses\Model\Agents;
use Storehouses\Model\Products;
use Storehouses\Model\Storehouses;

class CodeController extends DataApiCore{
    private $infoResponse = [];
    private $arrRequest = [];
    private $companyId;
    private $userId;
    
    public $entityPxtAuthentication;
    private $entityCodes;
    private $entityStorehouses;
    private $entityProducts;
    private $entityAgents;
    private $entityStatisticChecks;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Codes $entityCodes, Storehouses $entityStorehouses, 
    Products $entityProducts, Agents $entityAgents, StatisticChecks $entityStatisticChecks) {
        parent::__construct($entityPxtAuthentication);
        $this->entityPxtAuthentication  = $entityPxtAuthentication;
        $this->entityCodes              = $entityCodes;
        $this->entityStorehouses        = $entityStorehouses;
        $this->entityProducts           = $entityProducts;
        $this->entityAgents             = $entityAgents;
        $this->entityStatisticChecks    = $entityStatisticChecks;
    }
    
    public function indexAction(){
		  die("data-api code index");
    }
    
    public function checkCodeAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $phone = isset($this->arrRequest["phone"]) ? $this->arrRequest["phone"] : "";
        $code = isset($this->arrRequest["code"]) ? $this->arrRequest["code"] : "";
        $address = isset($this->arrRequest["address"]) ? $this->arrRequest["address"] : "";
        if($phone != "" && $code != ""){
            $url = "https://fortune.org/api/index/?Command_Code=APP&User_ID=" . $phone;
            $url .= "&Service_ID=APP&Request_ID=" . time() . "&Message=" . $code . "&address=" . $address;
            $url = str_replace(" ", "%20", $url);
            // echo $url; die();
            $dataSend = file_get_contents($url);
            $this->infoResponse["error"] = "0";
            $this->infoResponse["message"] = $dataSend;
        }else{
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Th??ng tin ch??a ?????y ?????";
        }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function checkAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $condition = isset($this->arrRequest["condition"]) ? $this->arrRequest["condition"] : "0";
        $keyword = isset($this->arrRequest["keyword"]) ? $this->arrRequest["keyword"] : "";
        
        if($keyword != ""){
            $arrCodes = $this->entityCodes->fetchAlls($this->companyId, ["condition" => $condition, "keyword" => $keyword]);
            $optionProducts    = $this->entityProducts->fetchAllOptions01(["company_id" => $this->companyId]);
            $optionAgents      = $this->entityAgents->fetchAllToOptions(["company_id" => $this->companyId]);
            $optionStorehouses = $this->entityStorehouses->fetchAllOptions(["company_id" => $this->companyId]);
            if(!empty($arrCodes)){
                for($i = 0; $i < count($arrCodes); $i++){
                    $arrCodes[$i]["product_name"] = isset($optionProducts[$arrCodes[$i]["product_id"]]) ? $optionProducts[$arrCodes[$i]["product_id"]] : "";
                    $arrCodes[$i]["agent_name"] = isset($optionAgents[$arrCodes[$i]["agent_id"]]) ? $optionAgents[$arrCodes[$i]["agent_id"]] : "";
                    $arrCodes[$i]["storehouse_name"] = isset($optionStorehouses[$arrCodes[$i]["storehouse_id"]]) ? $optionStorehouses[$arrCodes[$i]["storehouse_id"]] : "";
                    $arrCodes[$i]["status_name"] = ((int)$arrCodes[$i]["number_checked"] === 0) ? "Ch??a ???????c ki???m tra" : "???? ???????c ki???m tra";
                    if((int)$arrCodes[$i]["type_check"] === 2){
                        $arrCodes[$i]["type_name"] = "App";
                    }elseif((int)$arrCodes[$i]["type_check"] === 1){
                        $arrCodes[$i]["type_name"] = "QRCode";
                    }elseif((int)$arrCodes[$i]["type_check"] === 0){
                        $arrCodes[$i]["type_name"] = "SMS";
                    }
                    if($arrCodes[$i]["checked_at"] == null || $arrCodes[$i]["checked_at"] == ""){
                        $arrCodes[$i]["checked_at"] = "";
                        $arrCodes[$i]["type_name"] = "";
                        $arrCodes[$i]["phone_id"] = "";
                    }else{
                        $arrCodes[$i]["checked_at"] = date_format(date_create($arrCodes[$i]["checked_at"]), 'd/m/Y H:i:s');
                    }
                }
                $this->infoResponse["error"] = "0";
                $this->infoResponse["content"] = $arrCodes[0];
            }else{
                $this->infoResponse["error"] = "1";
                $this->infoResponse["message"] = "Kh??ng t??m th???y k???t qu???";
            }
        }else{
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Th??ng tin ch??a ?????y ?????";
        }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }

    private function checkPost(){
        if(!$this->getRequest()->isPost()){
            $this->infoResponse["error"] = "1";
            die(\Zend\Json\Encoder::encode($this->infoResponse));
        }
    }

    private function checkUser(){
        $username = isset($this->arrRequest["username"]) ? $this->arrRequest["username"] : "";
        $password = isset($this->arrRequest["password"]) ? $this->arrRequest["password"] : "";
        $this->companyId = isset($this->arrRequest["company_id"]) ? $this->arrRequest["company_id"] : "";
        if($this->companyId == ""){
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "L???i th??ng tin kh??ch h??ng, li??n h??? fortune ???? bi???t th??m th??ng tin";
            die(\Zend\Json\Encoder::encode($this->infoResponse));
        }
        if($username != "" && $password != ""){
            $userCurrent = $this->entityPxtAuthentication->checkUserExist2($username, $password);
            if(!empty($userCurrent)){
                // $this->companyId = $userCurrent["company_id"];
                $this->userId = $userCurrent["id"];
                return true;
            }else{
                $this->infoResponse["error"] = "1";
                $this->infoResponse["message"] = "T??i kho???n ho???c m???t kh???u kh??ng ch??nh x??c";
            }
        }else{
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Th??ng tin ch??a ?????y ?????";
        }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function statisticSuccessDateCurrentAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        $companyIdIn = isset($this->arrRequest["company_id"]) ? $this->arrRequest["company_id"] : "";
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        // L???y s??? l?????ng nh???n tin ng??y hi???n t???i
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = $this->entityStatisticChecks->fetchTotalSuccessDateCurrent($companyIdIn);
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function statisticSuccessAllAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        $companyIdIn = isset($this->arrRequest["company_id"]) ? $this->arrRequest["company_id"] : "";
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        // L???y s??? l?????ng nh???n tin ng??y hi???n t???i
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = $this->entityStatisticChecks->fetchTotalSuccessAll($companyIdIn);
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
}
