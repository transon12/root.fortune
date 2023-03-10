<?php

namespace DataApi\Controller;

use Admin\Model\PxtAuthentication;
use DataApi\Core\DataApiCore;
use Zend\Mvc\Controller\AbstractActionController;
use DataApi\Model\Agents;

class AgentsController extends DataApiCore{
    
    public $entityPxtAuthentication;
    private $entityAgents;
    private $companyId;
    private $userId;
    private $infoResponse = [];
    private $arrRequest = [];

    public function __construct(PxtAuthentication $entityPxtAuthentication, Agents $entityAgents) {
        parent::__construct($entityPxtAuthentication);
        $this->entityPxtAuthentication  = $entityPxtAuthentication;
        $this->entityAgents        = $entityAgents;
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
        $arrAgents = $this->entityAgents->fetchAlls(["company_id" => $this->companyId, "keyword" => $keyword, "page" => $page, "limit" => $limit]);
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = $arrAgents;
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function deleteAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $id = isset($this->arrRequest["id"]) ? $this->arrRequest["id"] : "";
        $this->entityAgents->updateRow($id, ['status' => '-1']);
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = "X??a d??? li???u th??nh c??ng";
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
        $address = isset($this->arrRequest["address"]) ? $this->arrRequest["address"] : "";
        $data = [
            'company_id' => $this->companyId,
            'user_id' => $this->userId,
            'name' => $name,
            'code' => $code,
            'address' => $address,
            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
        ];
        $this->entityAgents->addRow($data);
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = "Th??m d??? li???u th??nh c??ng";
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function editAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $id = isset($this->arrRequest["id"]) ? $this->arrRequest["id"] : "";
        $valueCurrent = $this->entityAgents->fetchRow($id);
        if(empty($valueCurrent)){
            $this->infoResponse["error"] = "1";
            $this->infoResponse["content"] = "Kh??ng t??m th???y ?????i l?? n??y";
        }else{
            $name = isset($this->arrRequest["name"]) ? $this->arrRequest["name"] : "";
            $code = isset($this->arrRequest["code"]) ? $this->arrRequest["code"] : "";
            $address = isset($this->arrRequest["address"]) ? $this->arrRequest["address"] : "";
            $data = [
                'name' => $name,
                'code' => $code,
                'address' => $address
            ];
            $this->entityAgents->updateRow($id, $data);
            $this->infoResponse["error"] = "0";
            $this->infoResponse["content"] = "S???a d??? li???u th??nh c??ng";
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
            $this->infoResponse["message"] = "L???i th??ng tin kh??ch h??ng, li??n h??? Fortune ???? bi???t th??m th??ng tin";
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
}
