<?php

namespace DataApi\Controller;

use Admin\Model\PxtAuthentication;
use Companies\Model\Addresses;
use Companies\Model\Surrogates;
use DataApi\Core\DataApiCore;
use DataApi\Model\OrderDetails;
use DataApi\Model\Orders;
use DataApi\Model\Technologies;
use Zend\Mvc\Controller\AbstractActionController;

class OrdersController extends DataApiCore
{

    public $entityPxtAuthentication;
    private $entityOrders;
    private $entityAddresses;
    private $entitySurrogates;
    private $entityOrderDetails;
    private $entityTechnologies;
    private $companyId;
    private $userId;
    private $infoResponse = [];
    private $arrRequest = [];

    public function __construct(PxtAuthentication $entityPxtAuthentication, Orders $entityOrders, Addresses $entityAddresses, Surrogates $entitySurrogates,
        OrderDetails $entityOrderDetails, Technologies $entityTechnologies)
    {
        parent::__construct($entityPxtAuthentication);
        $this->entityPxtAuthentication  = $entityPxtAuthentication;
        $this->entityOrders             = $entityOrders;
        $this->entityAddresses          = $entityAddresses;
        $this->entitySurrogates         = $entitySurrogates;
        $this->entityOrderDetails       = $entityOrderDetails;
        $this->entityTechnologies       = $entityTechnologies;
    }

    public function indexAction()
    {
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $limit = isset($this->arrRequest["limit"]) ? $this->arrRequest["limit"] : 10;
        $page = isset($this->arrRequest["page"]) ? $this->arrRequest["page"] : 1;
        $keyword = isset($this->arrRequest["keyword"]) ? $this->arrRequest["keyword"] : "";
        $arrOrders = $this->entityOrders->fetchAlls(["company_id" => $this->companyId, "keyword" => $keyword, "page" => $page, "limit" => $limit]);
        $optionAddresses = $this->entityAddresses->fetchAllToOptions($this->companyId);
        // \Zend\Debug\Debug::dump($optionAddresses);
        $optionSurrogates = $this->entitySurrogates->fetchAllToOptions($this->companyId);
        // \Zend\Debug\Debug::dump($optionSurrogates);
        // die();
        if (!empty($arrOrders)) {
            for ($i = 0; $i < count($arrOrders); $i++) {
                $arrOrders[$i]["address_name"] = isset($optionAddresses[$arrOrders[$i]["addresses_id"]]) ? $optionAddresses[$arrOrders[$i]["addresses_id"]] : "";
                $arrOrders[$i]["surrogate_name"] = isset($optionSurrogates[$arrOrders[$i]["surrogates_id"]]) ? $optionSurrogates[$arrOrders[$i]["surrogates_id"]] : "";
                $arrOrders[$i]["delivery_request"] = ($arrOrders[$i]["delivery_request"] != null) ? date("d/m/Y H:i:s", strtotime($arrOrders[$i]["delivery_request"])) : "";
                $arrOrders[$i]["delivery_finish"] = ($arrOrders[$i]["delivery_finish"] != null) ? date("d/m/Y H:i:s", strtotime($arrOrders[$i]["delivery_finish"])) : "";
                $arrOrders[$i]["created_at"] = ($arrOrders[$i]["created_at"] != null) ? date("d/m/Y H:i:s", strtotime($arrOrders[$i]["created_at"])) : "";
                /**
                 * Trạng thái đơn hàng:
                 * 2: đang trong tiến trình (mã màu: R:249 G:204 B:118 hoặc	#F9CC76)
                 * 3: đơn hàng bị trễ (mã màu: R:223 G:0 B:41 hoặc #DF0029)
                 * 1: đơn hàng hoàn tất (mã màu: R:91 G:189 B:43 hoặc #5BBD2B)
                 */
                $arrOrders[$i]["status_order"] = "2";
            }
        }
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = $arrOrders;
        die(\Zend\Json\Encoder::encode($this->infoResponse));
    }

    public function indexOptionAction()
    {
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $optionOrders = $this->entityOrders->fetchAllOptions01(["company_id" => $this->companyId]);
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = $optionOrders;
        die(\Zend\Json\Encoder::encode($this->infoResponse));
    }

    public function deleteAction()
    {
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $id = isset($this->arrRequest["id"]) ? $this->arrRequest["id"] : "";
        $this->entityOrders->updateRow($id, ['status' => '-1']);
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = "Xóa dữ liệu thành công";
        die(\Zend\Json\Encoder::encode($this->infoResponse));
    }

    public function addAction()
    {
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $code = isset($this->arrRequest["code"]) ? $this->arrRequest["code"] : "";
        // Kiểm tra mã đơn hàng đã tồn tại hay chưa
        $orderCurrent = $this->entityOrders->fetchRowAsCode($code);
        if (empty($orderCurrent)) {
            $price = isset($this->arrRequest["price"]) ? $this->arrRequest["price"] : "";
            $addressesId = isset($this->arrRequest["addresses_id"]) ? $this->arrRequest["addresses_id"] : "";
            $surrogatesId = isset($this->arrRequest["surrogates_id"]) ? $this->arrRequest["surrogates_id"] : "";
            $numberOrder = isset($this->arrRequest["number_order"]) ? $this->arrRequest["number_order"] : "";
            $deliveryRequest = isset($this->arrRequest["delivery_request"]) ? $this->arrRequest["delivery_request"] : "";
            $data = [
                'company_id' => $this->companyId,
                'code' => $code,
                'price' => $price,
                'addresses_id' => $addressesId,
                'surrogates_id' => $surrogatesId,
                'number_order' => $numberOrder,
                'delivery_request' => $deliveryRequest,
                'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
            ];
            $this->entityOrders->addRow($data);
            $this->infoResponse["error"] = "0";
            $this->infoResponse["content"] = "Thêm dữ liệu thành công";
        }else{
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Mã đơn hàng đã tồn tại trước đó";
        }
        die(\Zend\Json\Encoder::encode($this->infoResponse));
    }

    public function editAction()
    {
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $id = isset($this->arrRequest["id"]) ? $this->arrRequest["id"] : "";
        $valueCurrent = $this->entityOrders->fetchRow($id);
        if (empty($valueCurrent)) {
            $this->infoResponse["error"] = "1";
            $this->infoResponse["content"] = "Không tìm thấy sản phẩm này";
        } else {
            $code = isset($this->arrRequest["code"]) ? $this->arrRequest["code"] : "";
            $price = isset($this->arrRequest["price"]) ? $this->arrRequest["price"] : "";
            $addressesId = isset($this->arrRequest["addresses_id"]) ? $this->arrRequest["addresses_id"] : "";
            $surrogatesId = isset($this->arrRequest["surrogates_id"]) ? $this->arrRequest["surrogates_id"] : "";
            $numberOrder = isset($this->arrRequest["number_order"]) ? $this->arrRequest["number_order"] : "";
            $deliveryRequest = isset($this->arrRequest["delivery_request"]) ? $this->arrRequest["delivery_request"] : "";
            $data = [
                'code' => $code,
                'price' => $price,
                'addresses_id' => $addressesId,
                'surrogates_id' => $surrogatesId,
                'number_order' => $numberOrder,
                'delivery_request' => $deliveryRequest,
            ];
            $this->entityOrders->updateRow($id, $data);
            $this->infoResponse["error"] = "0";
            $this->infoResponse["content"] = "Sửa dữ liệu thành công";
        }
        die(\Zend\Json\Encoder::encode($this->infoResponse));
    }

    public function optionAddressesAction()
    {
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $arrAddresses = $this->entityAddresses->fetchAll([], $this->companyId);
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = $arrAddresses;
        die(\Zend\Json\Encoder::encode($this->infoResponse));
    }

    public function optionSurrogatesAction()
    {
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $arrSurrogates = $this->entitySurrogates->fetchAll([], $this->companyId);
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = $arrSurrogates;
        die(\Zend\Json\Encoder::encode($this->infoResponse));
    }

    private function checkPost()
    {
        if (!$this->getRequest()->isPost()) {
            $this->infoResponse["error"] = "1";
            die(\Zend\Json\Encoder::encode($this->infoResponse));
        }
    }

    public function orderDetailsAction()
    {
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->a
        $orderId = isset($this->arrRequest["order_id"]) ? $this->arrRequest["order_id"] : 10;
        if($orderId != ""){
            $arrOrderDetails = $this->entityOrderDetails->fetchAll($orderId);
            if (!empty($arrOrderDetails)) {
                $optionTechnologies = $this->entityTechnologies->fetchAllToOption();
                for ($i = 0; $i < count($arrOrderDetails); $i++) {
                    $arrOrderDetails[$i]["technology_name"] = isset($optionTechnologies[$arrOrderDetails[$i]["technologies_id"]]) ? $optionTechnologies[$arrOrderDetails[$i]["technologies_id"]] : "";
                    // $arrOrderDetails[$i]["time"] = ($arrOrderDetails[$i]["time"] != null) ? date("d/m/Y H:i:s", strtotime($arrOrderDetails[$i]["time"])) : "";
                    $arrOrderDetails[$i]["created_at"] = ($arrOrderDetails[$i]["created_at"] != null) ? date("d/m/Y H:i:s", strtotime($arrOrderDetails[$i]["created_at"])) : "";
                    /**
                     * Trạng thái đơn hàng:
                     * 2: đang trong tiến trình (mã màu: R:249 G:204 B:118 hoặc	#F9CC76)
                     * 3: đơn hàng bị trễ (mã màu: R:223 G:0 B:41 hoặc #DF0029)
                     * 1: đơn hàng hoàn tất (mã màu: R:91 G:189 B:43 hoặc #5BBD2B)
                     */
                }
            }
            $this->infoResponse["error"] = "0";
            $this->infoResponse["content"] = $arrOrderDetails;
        }else{
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Mã đơn hàng không tồn tại";
        }
        die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function orderDetailsAddAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $technologiesId = isset($this->arrRequest["technologies_id"]) ? $this->arrRequest["technologies_id"] : "";
        $orderId = isset($this->arrRequest["order_id"]) ? $this->arrRequest["order_id"] : "";
        $data = [
            'technologies_id' => $technologiesId,
            'orders_id' => $orderId,
            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
        ];
        $this->entityOrderDetails->addRow($data);
        $this->infoResponse["error"] = "0";
        $this->infoResponse["content"] = "Thêm dữ liệu thành công";
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function orderDetailsDeleteAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        $id = isset($this->arrRequest["id"]) ? $this->arrRequest["id"] : 0;
        if($id == 0){
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Công nghệ này không tồn tại";
        }else{
            $this->entityOrderDetails->updateRow($id, ['status' => '-1']);
            $this->infoResponse["error"] = "0";
            $this->infoResponse["content"] = "Xóa dữ liệu thành công";
        }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }

    private function checkUser()
    {
        $username = isset($this->arrRequest["username"]) ? $this->arrRequest["username"] : "";
        $password = isset($this->arrRequest["password"]) ? $this->arrRequest["password"] : "";
        $this->companyId = isset($this->arrRequest["company_id"]) ? $this->arrRequest["company_id"] : "";
        if ($this->companyId == "") {
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Lỗi thông tin khách hàng, liên hệ fortune đã biết thêm thông tin";
            die(\Zend\Json\Encoder::encode($this->infoResponse));
        }
        if ($username != "" && $password != "") {
            $userCurrent = $this->entityPxtAuthentication->checkUserExist2($username, $password);
            if (!empty($userCurrent)) {
                // $this->companyId = $userCurrent["company_id"];
                $this->userId = $userCurrent["id"];
                return true;
            } else {
                $this->infoResponse["error"] = "1";
                $this->infoResponse["message"] = "Tài khoản hoặc mật khẩu không chính xác";
            }
        } else {
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Thông tin chưa đầy đủ";
        }
        die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
}
