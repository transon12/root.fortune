<?php

namespace DataApi\Controller;

use Admin\Model\PxtAuthentication;
use DataApi\Model\Codes;
use DataApi\Core\DataApiCore;
use DataApi\Model\Agents;
use DataApi\Model\Products;
use Zend\Mvc\Controller\AbstractActionController;
use DataApi\Model\Storehouses;

class StorehousesController extends DataApiCore{
    
    public $entityPxtAuthentication;
    private $entityStorehouses;
    private $entityCodes;
    private $entityProducts;
    private $entityAgents;
    private $companyId;
    private $userId;
    private $infoResponse = [];
    private $arrRequest = [];

    public function __construct(PxtAuthentication $entityPxtAuthentication, Storehouses $entityStorehouses, Codes $entityCodes, 
        Products $entityProducts, Agents $entityAgents) {
            parent::__construct($entityPxtAuthentication);
        $this->entityPxtAuthentication  = $entityPxtAuthentication;
        $this->entityStorehouses        = $entityStorehouses;
        $this->entityCodes              = $entityCodes;
        $this->entityProducts           = $entityProducts;
        $this->entityAgents             = $entityAgents;
    }
    
    public function indexAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $limit = isset($this->arrRequest["limit"]) ? $this->arrRequest["limit"] : 10;
        $page = isset($this->arrRequest["page"]) ? $this->arrRequest["page"] : 1;
        $companyId = isset($this->arrRequest["company_id"]) ? $this->arrRequest["company_id"] : "";
        $keyword = isset($this->arrRequest["keyword"]) ? $this->arrRequest["keyword"] : "";
        $arrStorehouses = $this->entityStorehouses->fetchAlls(["company_id" => $companyId, "keyword" => $keyword, "page" => $page, "limit" => $limit]);
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = $arrStorehouses;
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function deleteAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $id = isset($this->arrRequest["id"]) ? $this->arrRequest["id"] : "";
        $arrStorehouses = $this->entityStorehouses->deleteRow($id);
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
        $address = isset($this->arrRequest["address"]) ? $this->arrRequest["address"] : "";
        $data = [
            'company_id' => $this->companyId,
            'user_id' => $this->userId,
            'name' => $name,
            'address' => $address,
            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
        ];
        $this->entityStorehouses->addRow($data);
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
        $valueCurrent = $this->entityStorehouses->fetchRow($id);
        if(empty($valueCurrent)){
            $this->infoResponse["error"] = "1";
            $this->infoResponse["content"] = "Không tìm thấy kho này";
        }else{
            $name = isset($this->arrRequest["name"]) ? $this->arrRequest["name"] : "";
            $address = isset($this->arrRequest["address"]) ? $this->arrRequest["address"] : "";
            $data = [
                'name' => $name,
                'address' => $address
            ];
            $this->entityStorehouses->updateRow($id, $data);
            $this->infoResponse["error"] = "0";
            $this->infoResponse["content"] = "Sửa dữ liệu thành công";
        }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function importAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $status = isset($this->arrRequest["status"]) ? $this->arrRequest["status"] : "1";
        $storehouseId = isset($this->arrRequest["storehouse_id"]) ? $this->arrRequest["storehouse_id"] : "";
        $productId = isset($this->arrRequest["product_id"]) ? $this->arrRequest["product_id"] : "";
        $serials = isset($this->arrRequest["serials"]) ? $this->arrRequest["serials"] : "";
        $datetimeImport = (isset($this->arrRequest['datetime_import']) && strlen($this->arrRequest['datetime_import']) > 0) ? $this->arrRequest['datetime_import'] : date("d/m/Y H:i:s", time());
        // echo json_encode($this->arrRequest); die();
        $options = [
            'is_serial' => '1',
            'value_begin' => null,
            'value_end' => null
        ];
        $arrQrcodes = explode('=', $serials);
        if(isset($arrQrcodes[1])){
            $options['value_begin'] = $arrQrcodes[1];
        }else{
            $arrSerials = explode('-', $serials);
            if(!isset($arrSerials[1])){
                $options['is_serial'] = 2; 
                $options['value_begin'] = trim($arrSerials[0]);
            }else{
                $options['is_serial'] = 3;
                $options['value_begin'] = trim($arrSerials[0]);
                $options['value_end'] = trim($arrSerials[1]);
            }
        }
        $arrCodes = $this->entityCodes->fetchAlls($this->companyId, ['imports' => '1', 'is_all' => '1'] + $options);
        // echo $status; die();
        if(empty($arrCodes) || (int)$status === 0 ){
            $importedAt = date_create_from_format('d/m/Y H:i:s', $datetimeImport);
            $data = [
                'storehouse_id' => ((int)$status === 0) ? null : $storehouseId,
                'imported_at' => ((int)$status === 0) ? null : date_format($importedAt, 'Y-m-d H:i:s'),
                'product_id' => ((int)$status === 0) ? null : $productId,
                'agent_id' => null,
                'exported_at' => null,
                'number_checked' => 0,
                'number_checked_qrcode' => 0,
                'phone_id' => null,
                'checked_at' => null
            ];
            // if(!empty($settingDatas)){
            //     foreach($settingDatas as $key => $item){
            //         $data[$key] = ($status == 1) ? $valuePost[$key] : null;
            //     }
            // }
            $result = $this->entityCodes->updatesRowAsCondition($this->companyId, ["data" => $data] + $options);
            // die('abc');
            if($result > 0){
                $this->infoResponse["error"] = "0";
                if((int)$status === 0){
                    $this->infoResponse["content"] = "Reset mã thành công!";
                }else{
                    $this->infoResponse["content"] = "Nhập kho thành công!";
                }
            }else{
                $this->infoResponse["error"] = "1";
                if($options['is_serial'] == 3){
                    $this->infoResponse["message"] = "Không tìm thấy chuỗi này!";
                }else{
                    $this->infoResponse["message"] = "Không tìm thấy tem này!";
                }
            }
        }else{
            $this->infoResponse["error"] = "1";
            if($options['is_serial'] == 3){
                $this->infoResponse["message"] = "Trong chuỗi này đã có tem được thêm vào trước đó!";
            }else{
                $this->infoResponse["message"] = "Tem này này đã được thêm vào trước đó!";
            }
        }
        // $valueCurrent = $this->entityStorehouses->fetchRow($id);
        // if(empty($valueCurrent)){
        //     $this->infoResponse["error"] = "1";
        //     $this->infoResponse["content"] = "Không tìm thấy kho này";
        // }else{
        //     $name = isset($this->arrRequest["name"]) ? $this->arrRequest["name"] : "";
        //     $address = isset($this->arrRequest["address"]) ? $this->arrRequest["address"] : "";
        //     $data = [
        //         'name' => $name,
        //         'address' => $address
        //     ];
        //     $this->entityStorehouses->updateRow($id, $data);
        //     $this->infoResponse["error"] = "0";
        //     $this->infoResponse["content"] = "Sửa dữ liệu thành công";
        // }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function statisticImportAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $id = isset($this->arrRequest["id"]) ? $this->arrRequest["id"] : "";
        $productId = isset($this->arrRequest["product_id"]) ? $this->arrRequest["product_id"] : "0";
        $limit = isset($this->arrRequest["limit"]) ? $this->arrRequest["limit"] : 10;
        $page = isset($this->arrRequest["page"]) ? $this->arrRequest["page"] : 1;
        $valueCurrent = $this->entityStorehouses->fetchRow($id);
        if(empty($valueCurrent)){
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Không tìm thấy kho này";
        }else{
            $arrCodes = $this->entityCodes->fetchCountByDate($this->companyId, ['type' => 'imported_at', 'storehouse_id' => $id, "page" => $page, "limit" => $limit, "product_id" => $productId]);
            if(!empty($arrCodes)){
                $optionProducts = $this->entityProducts->fetchAllOptions01(['company_id' => $this->companyId]);
                for($i = 0; $i < count($arrCodes); $i++){
                    $arrCodes[$i]["product_name"] = $optionProducts[$arrCodes[$i]["product_id"]];
                }
            }
            $this->infoResponse["error"] = "0";
            $this->infoResponse["content"] = $arrCodes;
        }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function exportAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $status = isset($this->arrRequest["status"]) ? $this->arrRequest["status"] : "1";
        $agentId = isset($this->arrRequest["agent_id"]) ? $this->arrRequest["agent_id"] : "";
        $serials = isset($this->arrRequest["serials"]) ? $this->arrRequest["serials"] : "";
        $datetimeExport = (isset($this->arrRequest['datetime_export']) && strlen($this->arrRequest['datetime_export']) > 0) ? $this->arrRequest['datetime_export'] : date("d/m/Y H:i:s", time());
        // echo json_encode($this->arrRequest); die();
        $options = [
            'is_serial' => '1',
            'value_begin' => null,
            'value_end' => null
        ];
        $arrQrcodes = explode('=', $serials);
        if(isset($arrQrcodes[1])){
            $options['value_begin'] = $arrQrcodes[1];
        }else{
            $arrSerials = explode('-', $serials);
            if(!isset($arrSerials[1])){
                $options['is_serial'] = 2; 
                $options['value_begin'] = trim($arrSerials[0]);
            }else{
                $options['is_serial'] = 3;
                $options['value_begin'] = trim($arrSerials[0]);
                $options['value_end'] = trim($arrSerials[1]);
            }
        }
        $arrCodes = $this->entityCodes->fetchAlls($this->companyId, ['exports' => '1', 'is_all' => '1', 'status' => $status] + $options);
        // echo $status; die();
        if(empty($arrCodes)){
            $exportedAt = date_create_from_format('d/m/Y H:i:s', $datetimeExport);
            $data = [
                'agent_id' => ((int)$status === 1) ? $agentId : null,
                'exported_at' => ((int)$status === 1) ? date_format($exportedAt, 'Y-m-d H:i:s') : null
            ];
            $result = $this->entityCodes->updatesRowAsCondition($this->companyId, ['data' => $data] + $options);
            
            if($result > 0){
                $this->infoResponse["error"] = "0";
                $this->infoResponse["content"] = ($status == 1) ? 'Thêm dữ liệu thành công!' : 'Xóa dữ liệu thành công!';
            }else{
                $this->infoResponse["error"] = "1";
                if($options['is_serial'] == 3){
                    $this->infoResponse["message"] = "Không tìm thấy chuỗi này!";
                }else{
                    $this->infoResponse["message"] = "Không tìm thấy tem này!";
                }
            }
        }else{
            $this->infoResponse["error"] = "1";
            if($options['is_serial'] == 3){
                $this->infoResponse["message"] = ((int)$status === 1) ? 'Trong chuỗi này đã có tem được xuất trước đó!' : 'Trong chuỗi này có tem chưa được xuất hoặc đã được xóa trước đó!';
            }else{
                $this->infoResponse["message"] = ($status == 1) ? 'Tem này này đã được xuất trước đó!' : 'Tem này chưa được xuất!';
            }
        }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function statisticExportAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $productId = isset($this->arrRequest["product_id"]) ? $this->arrRequest["product_id"] : "0";
        $id = isset($this->arrRequest["id"]) ? $this->arrRequest["id"] : "";
        $limit = isset($this->arrRequest["limit"]) ? $this->arrRequest["limit"] : 10;
        $page = isset($this->arrRequest["page"]) ? $this->arrRequest["page"] : 1;
        $valueCurrent = $this->entityAgents->fetchRow($id);
        if(empty($valueCurrent)){
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Không tìm thấy đại lý này";
        }else{
            $arrCodes = $this->entityCodes->fetchCountByDate($this->companyId, ['type' => 'exported_at', 'agent_id' => $id, "page" => $page, "limit" => $limit, "product_id" => $productId]);
            if(!empty($arrCodes)){
                $optionProducts = $this->entityProducts->fetchAllOptions01(['company_id' => $this->companyId]);
                for($i = 0; $i < count($arrCodes); $i++){
                    $arrCodes[$i]["product_name"] = isset($optionProducts[$arrCodes[$i]["product_id"]]) ? $optionProducts[$arrCodes[$i]["product_id"]] : "Không xác định";
                }
            }
            $this->infoResponse["error"] = "0";
            $this->infoResponse["content"] = $arrCodes;
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
