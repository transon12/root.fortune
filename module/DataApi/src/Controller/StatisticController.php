<?php

namespace DataApi\Controller;

// use Zend\Mvc\Controller\AbstractActionController;
use Admin\Model\PxtAuthentication;
use DataApi\Core\DataApiCore;
use DataApi\Model\Messages;
use Storehouses\Model\Products;

class StatisticController extends DataApiCore{
    
    public $entityPxtAuthentication;
    private $entityMessages;
    private $entityProducts;
    private $companyId;
    private $userId;
    private $infoResponse = [];
    private $arrRequest = [];

    public function __construct(PxtAuthentication $entityPxtAuthentication, Messages $entityMessages, Products $entityProducts) {
        parent::__construct($entityPxtAuthentication);
        $this->entityPxtAuthentication  = $entityPxtAuthentication;
        $this->entityMessages           = $entityMessages;
        $this->entityProducts           = $entityProducts;
    }

    public function indexAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest); die();
        $this->checkUser();
        $limit = isset($this->arrRequest["limit"]) ? $this->arrRequest["limit"] : 10;
        $page = isset($this->arrRequest["page"]) ? $this->arrRequest["page"] : 1;
        $arrMessages = $this->entityMessages->fetchAlls(["company_id" => $this->companyId, "page" => $page, "limit" => $limit]);
        // \Zend\Debug\Debug::dump($arrMessages); die();
        if(!empty($arrMessages)){
            $optionProducts = $this->entityProducts->fetchAllOptions01(['company_id' => $this->companyId]);
            for($i = 0; $i < count($arrMessages); $i++){
                $arrMessages[$i]["product_name"] = isset($optionProducts[$arrMessages[$i]["product_id"]]) ? $optionProducts[$arrMessages[$i]["product_id"]] : "Không xác định";
                if((int)$arrMessages[$i]["type"] === 2){
                    $arrMessages[$i]["type_name"] = "App";
                }elseif((int)$arrMessages[$i]["type"] === 1){
                    $arrMessages[$i]["type_name"] = "Quét QRCode";
                }else{
                    $arrMessages[$i]["type_name"] = "SMS";
                }
                $arrMessages[$i]["created_at"] = date_format(date_create($arrMessages[$i]["created_at"]), 'd/m/Y H:i:s');
                $arrMessages[$i]["code_id"] = ($arrMessages[$i]["code_id"] != null) ? $arrMessages[$i]["code_id"] : "";
                $arrMessages[$i]["code_serial"] = ($arrMessages[$i]["code_serial"] != null) ? $arrMessages[$i]["code_serial"] : "";
                $arrMessages[$i]["code_qrcode"] = ($arrMessages[$i]["code_qrcode"] != null) ? $arrMessages[$i]["code_qrcode"] : "";
                $arrMessages[$i]["product_id"] = ($arrMessages[$i]["product_id"] != null) ? $arrMessages[$i]["product_id"] : "";
                $arrMessages[$i]["code_agent"] = ($arrMessages[$i]["code_agent"] != null) ? $arrMessages[$i]["code_agent"] : "";
            }
        }
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = $arrMessages;
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
            $this->infoResponse["message"] = "Lỗi thông tin khách hàng, liên hệ fortune đã biết thêm thông tin";
            die(\Zend\Json\Encoder::encode($this->infoResponse));
        }
        if($username != "" && $password != ""){
            $userCurrent = $this->entityPxtAuthentication->checkUserExist2($username, $password);
            // \Zend\Debug\Debug::dump($userCurrent); die("a");
            if(!empty($userCurrent)){
                // $this->companyId = $userCurrent["company_id"];
                $this->userId = $userCurrent["id"];
                return true;
            }else{
                $this->infoResponse["error"] = "1";
                $this->infoResponse["message"] = "Tài khoản hoặc mật khẩu không chính xác";
            }
        }else{
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Thông tin chưa đầy đủ";
        }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
}
