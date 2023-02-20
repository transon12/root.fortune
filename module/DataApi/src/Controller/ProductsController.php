<?php

namespace DataApi\Controller;

use Admin\Model\PxtAuthentication;
use DataApi\Core\DataApiCore;
use DataApi\Model\Products;
use Zend\Mvc\Controller\AbstractActionController;

class ProductsController extends DataApiCore{
    
    public $entityPxtAuthentication;
    private $entityProducts;
    private $companyId;
    private $userId;
    private $infoResponse = [];
    private $arrRequest = [];

    public function __construct(PxtAuthentication $entityPxtAuthentication, Products $entityProducts) {
        parent::__construct($entityPxtAuthentication);
        $this->entityPxtAuthentication  = $entityPxtAuthentication;
        $this->entityProducts        = $entityProducts;
    }
    
    public function indexAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $limit = isset($this->arrRequest["limit"]) ? $this->arrRequest["limit"] : 10;
        $page = isset($this->arrRequest["page"]) ? $this->arrRequest["page"] : 1;
        $keyword = isset($this->arrRequest["keyword"]) ? $this->arrRequest["keyword"] : "";
        $arrProducts = $this->entityProducts->fetchAlls(["company_id" => $this->companyId, "keyword" => $keyword, "page" => $page, "limit" => $limit]);
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = $arrProducts;
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function indexOptionAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $optionProducts = $this->entityProducts->fetchAllOptions01(["company_id" => $this->companyId]);
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = $optionProducts;
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function deleteAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $id = isset($this->arrRequest["id"]) ? $this->arrRequest["id"] : "";
        $this->entityProducts->updateRow($id, ['status' => '-1']);
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
        $this->entityProducts->addRow($data);
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
        $valueCurrent = $this->entityProducts->fetchRow($id);
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
            $this->entityProducts->updateRow($id, $data);
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
