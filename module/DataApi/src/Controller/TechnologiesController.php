<?php

namespace DataApi\Controller;

use Admin\Model\PxtAuthentication;
use DataApi\Core\DataApiCore;
use DataApi\Model\OrderDetails;
use DataApi\Model\Technologies;
use Zend\Mvc\Controller\AbstractActionController;

class TechnologiesController extends DataApiCore{
    
    public $entityPxtAuthentication;
    private $entityTechnologies;
    private $entityOrderDetails;
    private $companyId;
    private $userId;
    private $infoResponse = [];
    private $arrRequest = [];

    public function __construct(PxtAuthentication $entityPxtAuthentication, Technologies $entityTechnologies, OrderDetails $entityOrderDetails) {
        parent::__construct($entityPxtAuthentication);
        $this->entityPxtAuthentication  = $entityPxtAuthentication;
        $this->entityTechnologies        = $entityTechnologies;
        $this->entityOrderDetails        = $entityOrderDetails;
    }
    
    public function indexAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $arrTechnologies = $this->entityTechnologies->fetchAlls();
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = $arrTechnologies;
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function indexOptionAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();

        $orderId = isset($this->arrRequest["order_id"]) ? $this->arrRequest["order_id"] : "";
        $id = isset($this->arrRequest["id"]) ? $this->arrRequest["id"] : 0;
        if($orderId != ""){
            // Lấy danh sách order detail để lấy mission id và loại trừ ra khi tìm kiếm
            $arrOrderDetails = $this->entityOrderDetails->fetchAll($orderId);
            // die(\Zend\Json\Encoder::encode($arrMissionDetails));
            $orderIdNotIn = "";
            if(!empty($arrOrderDetails) && $id == 0){
                $i = 0;
                foreach($arrOrderDetails as $item){
                    if($i != 0){
                        $orderIdNotIn .= ", ";
                    }
                    $orderIdNotIn .= "'" . $item["technologies_id"] . "'";
                    $i++;
                }
            }
            $optionTechnologies = $this->entityTechnologies->fetchAlls($orderIdNotIn);
            $this->infoResponse["error"] = "0";
            $this->infoResponse["content"] = $optionTechnologies;
        }else{
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Mã đơn hàng không tồn tại";
        }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function deleteAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $id = isset($this->arrRequest["id"]) ? $this->arrRequest["id"] : "";
        $this->entityTechnologies->updateRow($id, ['status' => '-1']);
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = "Xóa dữ liệu thành công";
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function addAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $name = isset($this->arrRequest["name"]) ? $this->arrRequest["name"] : "";
        $code = isset($this->arrRequest["code"]) ? $this->arrRequest["code"] : "";
        $barcode = isset($this->arrRequest["barcode"]) ? $this->arrRequest["barcode"] : "";
        $data = [
            'company_id' => $this->companyId,
            'name' => $name,
            'code' => $code,
            'barcode' => $barcode,
            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
        ];
        $this->entityTechnologies->addRow($data);
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = "Thêm dữ liệu thành công";
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function editAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $id = isset($this->arrRequest["id"]) ? $this->arrRequest["id"] : "";
        $valueCurrent = $this->entityTechnologies->fetchRow($id);
        if(empty($valueCurrent)){
            $this->infoResponse["error"] = "1";
            $this->infoResponse["content"] = "Không tìm thấy sản phẩm này";
        }else{
            $name = isset($this->arrRequest["name"]) ? $this->arrRequest["name"] : "";
            $code = isset($this->arrRequest["code"]) ? $this->arrRequest["code"] : "";
            $barcode = isset($this->arrRequest["barcode"]) ? $this->arrRequest["barcode"] : "";
            $data = [
                'name' => $name,
                'code' => $code,
                'barcode' => $barcode,
            ];
            $this->entityTechnologies->updateRow($id, $data);
            $this->infoResponse["error"] = "0";
            $this->infoResponse["content"] = "Sửa dữ liệu thành công";
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
            $this->infoResponse["message"] = "Lỗi thông tin khách hàng, liên hệ fortune đã biết thêm thông tin";
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
                $this->infoResponse["message"] = "Tài khoản hoặc mật khẩu không chính xác";
            }
        }else{
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Thông tin chưa đầy đủ";
        }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
}
