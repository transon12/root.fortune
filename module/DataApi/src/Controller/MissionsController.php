<?php

namespace DataApi\Controller;

use Admin\Model\PxtAuthentication;
use DataApi\Core\DataApiCore;
use DataApi\Model\MissionDetails;
use DataApi\Model\Missions;
use Zend\Mvc\Controller\AbstractActionController;

class MissionsController extends DataApiCore{
    
    public $entityPxtAuthentication;
    private $entityMissions;
    private $entityMissionDetails;
    private $companyId;
    private $userId;
    private $infoResponse = [];
    private $arrRequest = [];

    public function __construct(PxtAuthentication $entityPxtAuthentication, Missions $entityMissions, MissionDetails $entityMissionDetails) {
        parent::__construct($entityPxtAuthentication);
        $this->entityPxtAuthentication  = $entityPxtAuthentication;
        $this->entityMissions           = $entityMissions;
        $this->entityMissionDetails     = $entityMissionDetails;
    }
    
    public function indexAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();

        $orderId = isset($this->arrRequest["order_id"]) ? $this->arrRequest["order_id"] : "";
        if($orderId != ""){
            $optionMissions = $this->entityMissions->fetchAllOptions();
            $arrMissionDetails = $this->entityMissionDetails->fetchAll($orderId);
            if (!empty($arrMissionDetails)) {
                for ($i = 0; $i < count($arrMissionDetails); $i++) {
                    // $arrMissionDetails[$i]["address_name"] = isset($optionAddresses[$arrOrders[$i]["addresses_id"]]) ? $optionAddresses[$arrOrders[$i]["addresses_id"]] : "";
                    // $arrMissionDetails[$i]["surrogate_name"] = isset($optionSurrogates[$arrOrders[$i]["surrogates_id"]]) ? $optionSurrogates[$arrOrders[$i]["surrogates_id"]] : "";
                    $arrMissionDetails[$i]["begined_at"] = ($arrMissionDetails[$i]["begined_at"] != null) ? date("d/m/Y H:i:s", strtotime($arrMissionDetails[$i]["begined_at"])) : "";
                    $arrMissionDetails[$i]["expected_at"] = ($arrMissionDetails[$i]["expected_at"] != null) ? date("d/m/Y H:i:s", strtotime($arrMissionDetails[$i]["expected_at"])) : "";
                    $arrMissionDetails[$i]["ended_at"] = ($arrMissionDetails[$i]["ended_at"] != null) ? date("d/m/Y H:i:s", strtotime($arrMissionDetails[$i]["ended_at"])) : "";
                    // $arrMissionDetails[$i]["created_at"] = ($arrMissionDetails[$i]["created_at"] != null) ? date("d/m/Y H:i:s", strtotime($arrMissionDetails[$i]["created_at"])) : "";
                    // $arrMissionDetails[$i]["created_at"] = ($arrMissionDetails[$i]["created_at"] != null) ? date("d/m/Y H:i:s", strtotime($arrMissionDetails[$i]["created_at"])) : "";
                    $arrMissionDetails[$i]["created_at"] = ($arrMissionDetails[$i]["created_at"] != null) ? date("d/m/Y H:i:s", strtotime($arrMissionDetails[$i]["created_at"])) : "";
                    $arrMissionDetails[$i]["mission_name"] = isset($optionMissions[$arrMissionDetails[$i]["mission_id"]]) ? $optionMissions[$arrMissionDetails[$i]["mission_id"]] : "";
                }
            }
            $this->infoResponse["error"] = "0";
            $this->infoResponse["content"] = $arrMissionDetails;
        }else{
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "M?? ????n h??ng kh??ng t???n t???i";
        }
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
            // L???y danh s??ch order detail ????? l???y mission id v?? lo???i tr??? ra khi t??m ki???m
            $arrMissionDetails = $this->entityMissionDetails->fetchAll($orderId);
            // die(\Zend\Json\Encoder::encode($arrMissionDetails));
            $orderIdNotIn = "";
            if(!empty($arrMissionDetails) && $id == 0){
                $i = 0;
                foreach($arrMissionDetails as $item){
                    if($i != 0){
                        $orderIdNotIn .= ", ";
                    }
                    $orderIdNotIn .= "'" . $item["mission_id"] . "'";
                    $i++;
                }
            }
            $optionMissions = $this->entityMissions->fetchAlls($orderIdNotIn);
            $this->infoResponse["error"] = "0";
            $this->infoResponse["content"] = $optionMissions;
        }else{
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "M?? ????n h??ng kh??ng t???n t???i";
        }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function deleteAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $id = isset($this->arrRequest["id"]) ? $this->arrRequest["id"] : 0;
        if($id == 0){
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Chi ti???t ????n h??ng n??y kh??ng t???n t???i";
        }else{
            $this->entityMissionDetails->updateRow($id, ['status' => '-1']);
            $this->infoResponse["error"] = "0";
            $this->infoResponse["content"] = "X??a d??? li???u th??nh c??ng";
        }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function addAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $user = isset($this->arrRequest["user"]) ? $this->arrRequest["user"] : "";
        $orderId = isset($this->arrRequest["order_id"]) ? $this->arrRequest["order_id"] : "";
        $missionId = isset($this->arrRequest["mission_id"]) ? $this->arrRequest["mission_id"] : "";
        $beginedAt = isset($this->arrRequest["begined_at"]) ? $this->arrRequest["begined_at"] : "";
        $expectedAt = isset($this->arrRequest["expected_at"]) ? $this->arrRequest["expected_at"] : "";
        $data = [
            'user' => $user,
            'order_id' => $orderId,
            'mission_id' => $missionId,
            'begined_at' => $beginedAt,
            'expected_at' => $expectedAt,
            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
        ];
        $this->entityMissionDetails->addRow($data);
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
        $user = isset($this->arrRequest["user"]) ? $this->arrRequest["user"] : "";
        $orderId = isset($this->arrRequest["order_id"]) ? $this->arrRequest["order_id"] : "";
        $missionId = isset($this->arrRequest["mission_id"]) ? $this->arrRequest["mission_id"] : "";
        $beginedAt = isset($this->arrRequest["begined_at"]) ? $this->arrRequest["begined_at"] : "";
        $expectedAt = isset($this->arrRequest["expected_at"]) ? $this->arrRequest["expected_at"] : "";
        $valueCurrent = $this->entityMissionDetails->fetchRow($orderId, $id);
        if(empty($valueCurrent)){
            $this->infoResponse["error"] = "1";
            $this->infoResponse["content"] = "Kh??ng t??m th???y chi ti???t ????n h??ng n??y";
        }else{
            $data = [
                'user' => $user,
                'order_id' => $orderId,
                'begined_at' => $beginedAt,
                'expected_at' => $expectedAt,
            ];
            $this->entityMissionDetails->updateRow($id, $data);
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
}
