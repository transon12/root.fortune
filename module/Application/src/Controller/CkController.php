<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Codes\Model\Codes;
use Settings\Model\Companies;
use Application\Form\Index\Layout01Form;
use Application\Form\Index\Layout02Form;
use Settings\Model\Cities;
use Storehouses\Model\Products;
use Storehouses\Model\Agents;
use Settings\Model\Logs;
use Settings\Model\Settings;
use Application\Form\Index\Layout03Form;
use Pxt;
use Application\Form\Index\Layout04Form;
use Application\Form\Index\Layout05Form;
use Application\Form\Index\Layout06Form;
use Application\Form\Index\Layout07Form;
use Application\Form\Index\Layout09Form;
use Application\Form\Index\Layout10Form;
use Settings\Model\Wards;

class CkController extends AbstractActionController{

    private $entitySettings;
    private $entityCodes;
    private $entityCompanies;
    private $entityCities;
    private $entityProducts;
    private $entityAgent;
    private $entityLogs;
    private $entityWards;
    private $paramController;
    private $paramAction;
    private $routeName;
    private $defineCompanyId;
    private $display;
    private $displays;
    private $contentLayout;
    private $layoutScanQrcode;
    private $qrCode;
    private $currentCode;
    private $companyCurrent;
    
    public function __construct(Settings $entitySettings, Codes $entityCodes, Companies $entityCompanies, Cities $entityCities, Products $entityProducts, 
        Agents $entityAgent, Logs $entityLogs, Wards $entityWards) {
        $this->entitySettings = $entitySettings;
        $this->entityCodes = $entityCodes;
        $this->entityCompanies = $entityCompanies;
        $this->entityCities = $entityCities;
        $this->entityProducts = $entityProducts;
        $this->entityAgent = $entityAgent;
        $this->entityLogs = $entityLogs;
        $this->entityWards = $entityWards;
    }
    public function onDispatch(MvcEvent $e){
        // check company
        $this->defineCompanyId = COMPANY_ID;
        $this->companyCurrent = $this->entityCompanies->fetchRow(COMPANY_ID);
        $this->contentLayout = $this->entitySettings->fetchLayouts($this->defineCompanyId);
        $layout = 'index';
        // if(isset($this->contentLayout['scan_qrcode']['layout'])){
        //     if($this->contentLayout['scan_qrcode']['layout'] != ''){
        //         $layout = $this->contentLayout['scan_qrcode']['layout'];
        //     } 
        // }
        // $this->layoutScanQrcode = $this->contentLayout['scan_qrcode']['content'];
        if(isset($this->contentLayout['scan_id']['layout'])){
            if($this->contentLayout['scan_id']['layout'] != ''){
                $layout = $this->contentLayout['scan_id']['layout'];
            } 
        }
        $this->layoutScanQrcode = $this->contentLayout['scan_id']['content'];
        $this->display = $this->entitySettings->fetchLayouts($this->defineCompanyId);
        $this->displays = $this->entitySettings->fetchDisplays($this->defineCompanyId);
        // \Zend\Debug\Debug::dump($this->display); die();
        // Get 'controller' parameter.
        $this->paramController = $this->getEvent()->getRouteMatch()->getParam('controller');
        // Get 'action' parameter.
        $this->paramAction = $this->getEvent()->getRouteMatch()->getParam('action');
        // Get name of matched route.
        $this->routeName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        $arrRouteName = explode('/', $this->routeName);
        if(count($arrRouteName) == 1){
            $this->routeName .= '/' . $this->paramAction;
        }
        /**
         * add log when user is used
         */
        $arrParamController = explode('\\', $this->paramController);
        $dataLog = [
            'domain' => FULL_SERVER_NAME,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'module' => isset($arrParamController[0]) ? $arrParamController[0] : "",
            'controller' => isset($arrParamController[2]) ? $arrParamController[2] : "",
            'action' => $this->paramAction,
            'content_server' => json_encode($_SERVER),
            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
        ];
        $dataLog['param'] = json_encode($this->params()->fromQuery());
        $request = $this->getRequest();
        if($request->isPost()){
            $dataPosts = $request->getPost()->toArray();
            if(isset($dataPosts['password'])){
                $dataPosts['password'] = "*******";
            }
            $dataLog['post'] = json_encode($dataPosts + $request->getFiles()->toArray());
        }
        /**
         * add log when user is used
         */
        // change layout
        $layout = str_replace("_", "", $layout);
        $routeMatch = new \Zend\Router\RouteMatch(array(
            'controller' => $this->paramController, 'action' => $layout
        ));
        $routeMatch->setMatchedRouteName($this->routeName);
        $e->setRouteMatch($routeMatch);
        $dataLog['action'] = $layout;
        
        $this->entityLogs->addRow($dataLog);
        
        // update company_id
        new \Admin\Service\Authentication(["company_id" => COMPANY_ID]);

        //  if (in_array($_SERVER['REMOTE_ADDR'], array("113.172.175.70"))) {
        //     echo $_SERVER['REQUEST_URI']; 
        //     $arrFnd = explode('=', $_SERVER['REQUEST_URI']);
        //     \Zend\Debug\Debug::dump($arrFnd); 
        //     die();
        // }

        // check qrcode
        $this->id = $this->params()->fromQuery('s', '');
        
        // echo $this->id; die();
        if($this->id == ''){
            die('Không tìm thấy qrcode này');
        }
        $this->currentCode = $this->entityCodes->fetchRowId(COMPANY_ID, $this->id);
        // \Zend\Debug\Debug::dump($this->currentCode); die();
        if(empty($this->currentCode)){
            die('Không tìm thấy qrcode này');
        }
        
        $response = parent::onDispatch($e);
        return $response;
    }
    
    private function replaceInfo($str = "", $content = []){
        if(empty($content) || $str == "") return $str;
        foreach($content as $key => $value){
            $keyFind = '{' . $key . '}';
            $str = str_replace($keyFind, $value, $str);
        }
        return $str;
    }
    
    public function indexAction(){
        $view = new ViewModel();
        $this->layout()->setTemplate('check/layout');
        
        $view->setVariable('currentCode', $this->currentCode);
        // \Zend\Debug\Debug::dump($this->layoutScanQrcode);  die();
        $message = KEYWORD_SMS . "%20" . $this->defineCompanyId . "%20" . $this->currentCode['id'];
        $url = FULL_SERVER_NAME . "/api/index/?Command_Code=QRCODE&User_ID=" . time();
        $url .= "&Service_ID=QRCODE&Request_ID=" . time() . "&Message=" . $message;
        $dataSend = json_decode(file_get_contents($url), true);
        // die("abc");
        $arrProduct = $this->entityProducts->fetchRow($this->currentCode['product_id']);
        $datasProduct = ($arrProduct['datas'] != null) ? json_decode($arrProduct['datas'], true) : [];

        $arrAgent = $this->entityAgent->fetchRow($this->currentCode['agent_id']);
        $datasAgent = ($arrAgent['datas'] != null) ? json_decode($arrAgent['datas'], true) : [];
        $this->replaceLayout([
            "product"   => array_merge($arrProduct, $datasProduct),
            "agent"     => array_merge($arrAgent, $datasAgent),
            "message"   => $dataSend['message'],
            "color"     => ($this->currentCode['number_checked_qrcode'] == 0) ? "#31AA38" : (($this->currentCode['number_checked_qrcode'] < 5) ? "#F07129" : "#ed3237"),
        ]);
        
        $view->setVariable('infoDisplay', $this->layoutScanQrcode);
        $this->layout()->setTemplate('info/layout');
        $view->setTemplate('application/index/index-info');

        $view->setVariable('displays', $this->displays);
        return $view;
    }
    
    public function layout01Action(){
        //die("Vào layout 01");
        $view = new ViewModel();
        $this->layout()->setTemplate('layout_01');

        $view->setVariable('currentCode', $this->currentCode);
        
        $form = new Layout01Form();
        $view->setVariable('form', $form);
        
        $optionCities = $this->entityCities->fetchAllOptions(['country_id' => 'VN']);
        $form->get('cities_id')->setValueOptions( ['' => '--- Chọn một khu vực ---' ] + $optionCities );
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if(!isset($optionCities[$valuePost['cities_id']])){
                $form->get('cities_id')->setMessages(['not_found' => 'Phải chọn một khu vực.']);
                $isValid = false;
            }
            if($isValid){
                
 				$fullname = str_replace(' ', '%20', $valuePost['fullname']);
                $phone = \Pxt\String\ChangeString::convertPhoneVn($valuePost['phone']);
 				$nameCity = str_replace(' ', '%20', $optionCities[$valuePost['cities_id']]);
 				$message = KEYWORD_SMS . "%20" . COMPANY_ID . "%20" . $this->currentCode['id'];
 				
                $url = FULL_SERVER_NAME . "/api/index/?Command_Code=QRCODE&User_ID=" . $phone;
                $url .= "&Service_ID=QRCODE&Request_ID=" . time() . "&Message=" . $message;
                $url .= "&fullname=" . $fullname;
                $url .= "&name_city=" . $nameCity;
                // $dataContent = file_get_contents($url);
                
                $dataSend = json_decode(file_get_contents($url), true);
                // \Zend\Debug\Debug::dump($dataSend); die();
                $arrProduct = $this->entityProducts->fetchRow($this->currentCode['product_id']);
                $datasProduct = (isset($arrProduct['datas']) && $arrProduct['datas'] != null) ? json_decode($arrProduct['datas'], true) : [];

                $arrAgent = $this->entityAgent->fetchRow($this->currentCode['agent_id']);
                $datasAgent = (isset($arrAgent['datas']) && $arrAgent['datas'] != null) ? json_decode($arrAgent['datas'], true) : [];
                
                $this->replaceLayout([
                    "product" => array_merge($arrProduct, $datasProduct),
                    "agent" => array_merge($arrAgent, $datasAgent),
                    "message" => $dataSend['message']
                ]);
                
                $view->setVariable('infoDisplay', $this->layoutScanQrcode);
                $this->layout()->setTemplate('layout_01_info');
                $view->setTemplate('application/index/layout01-info');
            }
        }

        $view->setVariable('displays', $this->displays);
        return $view;
    }
    
    private function replaceLayout($options = null){
        $code = isset($this->currentCode) ? $this->currentCode : null;
        $storehouse = isset($options["storehouse"]) ? $options["storehouse"] : null;
        $product = isset($options["product"]) ? $options["product"] : null;
        $agent = isset($options["agent"]) ? $options["agent"] : null;
        $message = isset($options["message"]) ? $options["message"] : "";
        $color = isset($options["color"]) ? $options["color"] : "";

        $timeCurrent = strtotime(\Pxt\Datetime\ChangeDatetime::getDatetimeCurrent());
        $this->layoutScanQrcode = str_replace("{product_description}", ( isset($product["description"]) ? $product["description"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{product_code}", ( isset($product["code"]) ? $product["code"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{product_barcode}", ( isset($product["barcode"]) ? $product["barcode"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{product_name}", ( isset($product["name"]) ? $product["name"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{product_image}", ( isset($product["image"]) ? ( $product["image"]) : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{product_uses}", ( isset($product["uses"]) ? $product["uses"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{product_producer}", ( isset($product["producer"]) ? $product["producer"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{product_guide}", ( isset($product["guide"]) ? $product["guide"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{product_manufacture}", ( isset($product["manufacture"]) ? $product["manufacture"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{product_expiry_date}", ( isset($product["expiry_date"]) ? $product["expiry_date"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{product_process}", ( isset($product["process"]) ? $product["process"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{import_hsd}", ( isset($code["hsd"]) ? $code["hsd"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{import_sodk}", ( isset($code["sodk"]) ? $code["sodk"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{import_ttkh}", ( isset($code["ttkh"]) ? $code["ttkh"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{import_voucher}", ( isset($code["voucher"]) ? $code["voucher"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{day}", date("d", $timeCurrent), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{month}", date("m", $timeCurrent), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{year}", date("Y", $timeCurrent), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{message}", $message, $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{color}", $color, $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{logo}", ( isset($this->displays['logo']) ? ( URL_UPLOAD . $this->displays['logo']) : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{image_check}", (TEMPS_IMG . (($code['number_checked_qrcode'] >= 5) ? 'check-checked' : ('check-' . $code['number_checked_qrcode'])) . ".png"), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{color_check}", (($code['number_checked_qrcode'] > 1) ? '#FFCC00' : '#00FF00'), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{id}", ( isset($code["id"]) ? $code["id"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{serial}", ( isset($code["serial"]) ? $code["serial"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{qrcode}", ( isset($code["qrcode"]) ? $code["qrcode"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{phone_id}", ( isset($code["phone_id"]) ? $code["phone_id"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{number_checked}", ( isset($code["number_checked"]) ? ((int)$code["number_checked"] + 1) : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{number_checked_qrcode}", ( isset($code["number_checked_qrcode"]) ? ((int)$code["number_checked_qrcode"] + 1) : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{checked_at}", ( isset($code["checked_at"]) ? date('d/m/Y - H:i:s', strtotime($code["checked_at"])) : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{imported_at}", ( isset($code["imported_at"]) ? date('d/m/Y - H:i:s', strtotime($code["imported_at"])) : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{exported_at}", ( isset($code["exported_at"]) ? date('d/m/Y - H:i:s', strtotime($code["exported_at"])) : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{storehouse_name}", ( isset($storehouse["name"]) ? $storehouse["name"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{storehouse_address}", ( isset($storehouse["address"]) ? $storehouse["address"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{agent_code}", ( isset($agent["code"]) ? $agent["code"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{agent_name}", ( isset($agent["name"]) ? $agent["name"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{agent_map}", ( isset($agent["map"]) ? $agent["map"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{agent_description}", ( isset($agent["description"]) ? $agent["description"] : "" ), $this->layoutScanQrcode);
        $this->layoutScanQrcode = str_replace("{agent_full_address}", ( isset($agent["address"]) ? ( $agent["address"] . $this->entityWards->fetchFullAddress($agent['ward_id']) ) : "" ), $this->layoutScanQrcode);
        
    }
    
    public function layout02Action(){
        //die("Vào layout 01");
        $view = new ViewModel();
        $this->layout()->setTemplate('layout_02');
        // check qrcode
        $qrCode = $this->params()->fromQuery('qr', '');
        if($qrCode == ''){
            die('Không tìm thấy qrcode này');
        }
        $currentCode = $this->entityCodes->fetchRowAsQrcode($qrCode, COMPANY_ID);
        if(empty($currentCode)){
            die('Không tìm thấy qrcode này');
        }
        $view->setVariable('currentCode', $currentCode);
        
        $form = new Layout02Form();
        $view->setVariable('form', $form);
        
        $optionCities = $this->entityCities->fetchAllOptions(['country_id' => 'VN']);
        $form->get('cities_id')->setValueOptions( ['' => '--- Chọn một khu vực ---' ] + $optionCities );
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if(!isset($optionCities[$valuePost['cities_id']])){
                $form->get('cities_id')->setMessages(['not_found' => 'Phải chọn một khu vực.']);
                $isValid = false;
            }
            if($isValid){
 				$nameCity = str_replace(' ', '%20', $optionCities[$valuePost['cities_id']]);
                $phone = \Pxt\String\ChangeString::convertPhoneVn($valuePost['phone']);
 				$message = KEYWORD_SMS . "%20" . COMPANY_ID;
 				
                $url = FULL_SERVER_NAME . "/api/index/?Command_Code=QRCODE&User_ID=" . $phone;
                $url .= "&Service_ID=QRCODE&Request_ID=" . time() . "&Message=" . $message;
                $url .= "&name_city=" . $nameCity;
                $dataSend = json_decode(file_get_contents($url), true);
                
                $arrProduct = $this->entityProducts->fetchRow($this->currentCode['product_id']);
                $datasProduct = ($arrProduct['datas'] != null) ? json_decode($arrProduct['datas'], true) : [];

                $arrAgent = $this->entityAgent->fetchRow($this->currentCode['agent_id']);
                $datasAgent = ($arrAgent['datas'] != null) ? json_decode($arrAgent['datas'], true) : [];
                $this->replaceLayout([
                    "product" => array_merge($arrProduct, $datasProduct),
                    "agent" => array_merge($arrAgent, $datasAgent),
                    "message" => $dataSend['message']
                ]);
                
                $view->setVariable('infoDisplay', $this->layoutScanQrcode);
                $this->layout()->setTemplate('layout_02_info');
                $view->setTemplate('application/index/layout02-info');
            }
        }

        $view->setVariable('displays', $this->displays);
        return $view;
    }
    
    public function layout03Action(){
        // die("Vào layout 01");
        $view = new ViewModel();
        $this->layout()->setTemplate('layout_03');
        // check qrcode
        $qrCode = $this->params()->fromQuery('s', '');
        if($qrCode == ''){
            die('Không tìm thấy qrcode này');
        }
        $currentCode = $this->entityCodes->fetchRowAsQrcode($qrCode, COMPANY_ID);
        if(empty($currentCode)){
            die('Không tìm thấy qrcode này');
        }
        // die(COMPANY_ID);
        if($currentCode["number_checked"] != "0"){
            if(COMPANY_ID == "TL"){
                die("<h3 style='text-align: center; color: #F00;'>Sản phẩm này đã được kích hoạt bảo hành. Mọi chi tiết vui lòng liên hệ hotline 0904069955</h3>");
            }elseif(COMPANY_ID == "VNG"){
                die("<h3 style='text-align: center; color: #F00;'>Sản phẩm này đã được kích hoạt bảo hành. Mọi chi tiết vui long liên hệ hotline 024.3987.8361</h3>");
            }
            // else{
            //     die("QRCode này đã được kiểm tra");
            // }
        }
        $view->setVariable('currentCode', $currentCode);
        
        $form = new Layout03Form();
        $view->setVariable('form', $form);
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if($isValid){
                
 				//$fullname = str_replace(' ', '%20', $valuePost['fullname']);
                $phone = \Pxt\String\ChangeString::convertPhoneVn($valuePost['phone']);
 				$message = KEYWORD_SMS . "%20" . COMPANY_ID . "%20" . $currentCode['id'];
 				
                $url = FULL_SERVER_NAME . "/api/index/?Command_Code=QRCODE&User_ID=" . $phone;
                $url .= "&Service_ID=QRCODE&Request_ID=" . time() . "&Message=" . $message;
                $dataSend = json_decode(file_get_contents($url), true);
                
                $arrProduct = $this->entityProducts->fetchRow($this->currentCode['product_id']);
                $datasProduct = ($arrProduct['datas'] != null) ? json_decode($arrProduct['datas'], true) : [];

                $arrAgent = $this->entityAgent->fetchRow($this->currentCode['agent_id']);
                $datasAgent = ($arrAgent['datas'] != null) ? json_decode($arrAgent['datas'], true) : [];
                $this->replaceLayout([
                    "product" => array_merge($arrProduct, $datasProduct),
                    "agent" => array_merge($arrAgent, $datasAgent),
                    "message" => $dataSend['message']
                ]);
                
                $view->setVariable('infoDisplay', $this->layoutScanQrcode);
                $this->layout()->setTemplate('layout_01_info');
                $view->setTemplate('application/index/layout01-info');
            }
        }

        $view->setVariable('displays', $this->displays);
        return $view;
    }
    
    public function layout04Action(){
        //die("Vào layout 01");
        $view = new ViewModel();
        $this->layout()->setTemplate('layout_04');
        // check qrcode
        $qrCode = $this->params()->fromQuery('qr', '');
        if($qrCode == ''){
            die('Không tìm thấy qrcode này');
        }
        $currentCode = $this->entityCodes->fetchRowAsQrcode($qrCode, COMPANY_ID);
        if(empty($currentCode)){
            die('Không tìm thấy qrcode này');
        }
        $view->setVariable('currentCode', $currentCode);
        
        $form = new Layout04Form();
        $view->setVariable('form', $form);
        
        $optionCities = $this->entityCities->fetchAllOptions(['country_id' => 'VN']);
//         $form->get('cities_id')->setValueOptions( ['' => '--- Chọn một khu vực ---' ] + $optionCities );
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
//             if(!isset($optionCities[$valuePost['cities_id']])){
//                 $form->get('cities_id')->setMessages(['not_found' => 'Phải chọn một khu vực.']);
//                 $isValid = false;
//             }
            if($isValid){
                $arrProduct = $this->entityProducts->fetchRow($currentCode['product_id']);
                $datasProduct = json_decode($arrProduct['datas'], true);
                $arrAgent = $this->entityAgent->fetchRow($currentCode['agent_id']);
                $cityId = 0;
                if($arrAgent['ward_id'] != '' && $arrAgent['ward_id'] != null){
                    $idAddress = $this->entityWards->fetchFullId($arrAgent['ward_id']);
                    $cityId = isset($idAddress['city_id']) ? $idAddress['city_id'] : '0';
                }
//                  die();
 				$fullname = str_replace(' ', '%20', $valuePost['fullname']);
                $phone = \Pxt\String\ChangeString::convertPhoneVn($valuePost['phone']);
 				$nameCity = str_replace(' ', '%20', (isset($optionCities[$cityId]) ? $optionCities[$cityId] : ""));
 				$message = KEYWORD_SMS . "%20" . COMPANY_ID . "%20" . $currentCode['id'];
 				
                $url = FULL_SERVER_NAME . "/api/index/?Command_Code=QRCODE&User_ID=" . $phone;
                $url .= "&Service_ID=QRCODE&Request_ID=" . time() . "&Message=" . $message;
                $url .= "&fullname=" . $fullname;
                $url .= "&name_city=" . $nameCity;
                $dataSend = json_decode(file_get_contents($url), true);
//                 echo $url;
//                 echo "<br />";
//                 echo "-" . $dataSend . "-";
                //\Zend\Debug\Debug::dump($datasProduct); die();
                
                
                $arrProduct = $this->entityProducts->fetchRow($this->currentCode['product_id']);
                $datasProduct = ($arrProduct['datas'] != null) ? json_decode($arrProduct['datas'], true) : [];

                $arrAgent = $this->entityAgent->fetchRow($this->currentCode['agent_id']);
                $datasAgent = ($arrAgent['datas'] != null) ? json_decode($arrAgent['datas'], true) : [];
                $this->replaceLayout([
                    "product" => array_merge($arrProduct, $datasProduct),
                    "agent" => array_merge($arrAgent, $datasAgent),
                    "message" => $dataSend['message']
                ]);
                
                
                $view->setVariable('infoDisplay', $this->layoutScanQrcode);
                $this->layout()->setTemplate('layout_04_info');
                $view->setTemplate('application/index/layout04-info');
            }
        }

        $view->setVariable('displays', $this->displays);
        return $view;
    }
    
    public function layout05Action(){
        $view = new ViewModel();
        $this->layout()->setTemplate('layout_05');
        $view->setVariable('currentCode', $this->currentCode);
        
        $form = new Layout05Form;
        $view->setVariable('form', $form);
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if($this->currentCode["id"] != $valuePost["code"]){
                $form->get('code')->setMessages($form->get('code')->getMessages() + ['code_incorrect' => 'Mã pin không chính xác!']);
                $isValid = false;
            }

            if($isValid){
 				$fullname = str_replace(' ', '%20', $valuePost['fullname']);
                $phone = \Pxt\String\ChangeString::convertPhoneVn($valuePost['phone']);
 				$message = KEYWORD_SMS . "%20" . COMPANY_ID . "%20" . $this->currentCode['id'];
 				
                $url = FULL_SERVER_NAME . "/api/index/?Command_Code=QRCODE&User_ID=" . $phone;
                $url .= "&Service_ID=QRCODE&Request_ID=" . time() . "&Message=" . $message;
                $url .= "&fullname=" . $fullname;
                $dataSend = json_decode(file_get_contents($url), true);
                // \Zend\Debug\Debug::dump($dataSend); 
                $arrProduct = $this->entityProducts->fetchRow($this->currentCode['product_id']);
                $datasProduct = ($arrProduct['datas'] != null) ? json_decode($arrProduct['datas'], true) : [];

                $arrAgent = $this->entityAgent->fetchRow($this->currentCode['agent_id']);
                $datasAgent = ($arrAgent['datas'] != null) ? json_decode($arrAgent['datas'], true) : [];
                // \Zend\Debug\Debug::dump($arrAgent); 
                // \Zend\Debug\Debug::dump($datasAgent); die();
                // re update $this->currentCode
                if($this->currentCode["number_checked_qrcode"] < 1){
                    $this->currentCode["phone_id"] = $phone;
                    $this->currentCode["checked_at"] = \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent();
                }
                $this->replaceLayout([
                    "product" => array_merge($arrProduct, $datasProduct),
                    "agent" => array_merge($arrAgent, $datasAgent),
                    "message" => $dataSend['message']
                ]);
                
                $view->setVariable('infoDisplay', $this->layoutScanQrcode);
                $this->layout()->setTemplate('layout_05_info');
                $view->setTemplate('application/index/layout05-info');
            }
        }

        $view->setVariable('displays', $this->displays);
        return $view;
    }
    
    public function layout06Action(){
        $view = new ViewModel();
        $this->layout()->setTemplate('layout_06');
        $view->setVariable('currentCode', $this->currentCode);
        
        $form = new Layout06Form;

        $view->setVariable('form', $form);
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if($isValid){
                $arrProduct = $this->entityProducts->fetchRow($this->currentCode['product_id']);
                $datasProduct = ($arrProduct['datas'] != null) ? json_decode($arrProduct['datas'], true) : [];

                $arrAgent = $this->entityAgent->fetchRow($this->currentCode['agent_id']);
                $datasAgent = ($arrAgent['datas'] != null) ? json_decode($arrAgent['datas'], true) : [];

 				$fullname = str_replace(' ', '%20', $valuePost['fullname']);
                $phone = \Pxt\String\ChangeString::convertPhoneVn($valuePost['phone']);
 				$message = KEYWORD_SMS . "%20" . COMPANY_ID . "%20" . $this->currentCode['id'];
 				
                $url = FULL_SERVER_NAME . "/api/index/?Command_Code=QRCODE&User_ID=" . $phone;
                $url .= "&Service_ID=QRCODE&Request_ID=" . time() . "&Message=" . $message;
                $url .= "&fullname=" . $fullname;
                $dataSend = json_decode(file_get_contents($url), true);
                
                $this->replaceLayout([
                    "product" => array_merge($arrProduct, $datasProduct),
                    "agent" => array_merge($arrAgent, $datasAgent),
                    "message" => $dataSend['message']
                ]);
                
                $view->setVariable('infoDisplay', $this->layoutScanQrcode);
                $this->layout()->setTemplate('layout_06_info');
                $view->setTemplate('application/index/layout06-info');
            }
        }

        $view->setVariable('displays', $this->displays);
        return $view;
    }
    
    public function layout07Action(){
        $view = new ViewModel();
        $this->layout()->setTemplate('layout_07');
        $view->setVariable('currentCode', $this->currentCode);
        
        $form = new Layout07Form();

        $view->setVariable('form', $form);
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            $arrAgent = [];
            if($isValid){
                $arrProduct = $this->entityProducts->fetchRow($this->currentCode['product_id']);
                $datasProduct = ($arrProduct['datas'] != null) ? json_decode($arrProduct['datas'], true) : [];

                $arrAgent = $this->entityAgent->fetchRow($this->currentCode['agent_id']);
                $datasAgent = ($arrAgent['datas'] != null) ? json_decode($arrAgent['datas'], true) : [];

                // $fullname = str_replace(' ', '%20', $valuePost['fullname']);
                // $address = str_replace(' ', '%20', $valuePost['address']);
                $phone = \Pxt\String\ChangeString::convertPhoneVn($valuePost['phone']);
 				$message = KEYWORD_SMS . "%20" . COMPANY_ID . "%20" . $this->currentCode['id'];
                // \Zend\Debug\Debug::dump($arrAgent); die();
                if($this->currentCode["number_checked"] != "0"){
                    if((COMPANY_ID == "TAMAN" && $this->currentCode["phone_id"] != $phone) || $this->currentCode["number_checked"] >= 3){
                       die("<h2 style='margin-top: 100px;text-align: center;color: #F00;font-size: 62px;'>Mã Qrcode này đã được kiểm tra. Vui lòng liên hệ Tâm An Cosmetics, Hotline: 0901 311 422 - 0903 812 105 để được hỗ trợ.  Trân trọng cảm ơn quý khách. Website: <a href='www.myphamthiennhientaman.com'>www.myphamthiennhientaman.com</a> hoặc <a href='www.carenel.vn'>www.carenel.vn</a></h2>");
                    }
                }
 				
                $url = FULL_SERVER_NAME . "/api/index/?Command_Code=QRCODE&User_ID=" . $phone;
                $url .= "&Service_ID=QRCODE&Request_ID=" . time() . "&Message=" . $message;
                // $url .= "&fullname=" . $fullname;
                // $url .= "&address=" . $address;
                $dataSend = json_decode(file_get_contents($url), true);
                
                $this->replaceLayout([
                    "product" => array_merge($arrProduct, $datasProduct),
                    "agent" => array_merge($arrAgent, $datasAgent),
                    "message" => $dataSend['message']
                ]);
                
                $view->setVariable('infoDisplay', $this->layoutScanQrcode);
                $this->layout()->setTemplate('layout_07_info');
                $view->setTemplate('application/index/layout07-info');
            }
        }

        $view->setVariable('displays', $this->displays);
        return $view;
    }

    public function layout08Action(){
        if($this->defineCompanyId == 'FB'){
            header("Location: https://www-fbvn.fujifilm.com/vi-VN/Support-and-Drivers/Genuine-Toner");
        }
        if($this->defineCompanyId == 'SHK'){
            header("Location: https://sheeskin.vn/");
        }
        if($this->defineCompanyId == 'TTL1'){
            header("Location: https://tamtriluc.com/sach-va-an-pham/");
        }
        if($this->defineCompanyId == 'VHM'){
            header("Location: https://miubrandth.com/");
        }
        if($this->defineCompanyId == 'FND'){
            header("Location:" . $this->currentCode['link']);
        }
        if($this->defineCompanyId == 'PES'){
            header("Location:" . $this->currentCode['link']);
        }
        if($this->defineCompanyId == "ZENNY"){
            echo '<!DOCTYPE html><html><head><title>SMS</title><meta http-equiv="Content-Type" content="text/html; charset=\'UTF-8\'" ><script src="https://code.jquery.com/jquery-3.5.0.js"></script></head><body><div style="width: 100%; margin-top: 100px; text-align: center;"><a href="sms:8077?&body=CHG ZENNY ' . $this->currentCode["id"] . '" style="font-size: 64px">Nhan de gui tin SMS</a></div><script type="text/javascript">$(function(){window.location.href = $("a").attr("href");});</script></body></html>';
        }
        if($this->defineCompanyId == 'AU'){
            header("Location: https://bodybuilding.vn/pages/san-pham-ban-mua-co-an-toan");
        }
        if($this->defineCompanyId == 'H2'){
            header("Location: https://ruoungotaybac.com/");
        }
        // if($this->defineCompanyId == 'PHP'){
        //     header("Location: https://www.google.com/");
        // }
        die();
    }
    
    public function layout09Action(){
        $view = new ViewModel();
        $this->layout()->setTemplate('layout_09');
        $view->setVariable('currentCode', $this->currentCode);
        
        $form = new Layout09Form;
        $view->setVariable('form', $form);
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if($this->currentCode["id"] != $valuePost["code"]){
                $form->get('code')->setMessages($form->get('code')->getMessages() + ['code_incorrect' => 'Mã pin không chính xác!']);
                $isValid = false;
            }

            if($isValid){
 				$fullname = str_replace(' ', '%20', $valuePost['fullname']);
                $phone = \Pxt\String\ChangeString::convertPhoneVn($valuePost['phone']);
 				$message = KEYWORD_SMS . "%20" . COMPANY_ID . "%20" . $this->currentCode['id'];
 				
                $url = FULL_SERVER_NAME . "/api/index/?Command_Code=QRCODE&User_ID=" . $phone;
                $url .= "&Service_ID=QRCODE&Request_ID=" . time() . "&Message=" . $message;
                $url .= "&fullname=" . $fullname;
                $dataSend = file_get_contents($url);
                $dataJson = json_decode($dataSend, true);
                if(!isset($dataJson['status'])){
                    die('Not found');
                }
                $allowCheckedLimit = isset($companyCurrent['allow_checked_limit']) ? $companyCurrent['allow_checked_limit']:100;
                if($dataJson['status'] == 1){
                    $mes = '<p style="text-align: center;font-size:10px;font-family:Arial,Helvetica,sans-serif"><span style="color: #ff0000;"><strong>SẢN</strong><strong> PHẨM BẠN ĐANG SỬ DỤNG L&Agrave; ETIAXIL CH&Iacute;NH H&Atilde;NG!</strong></span></p><p style="text-align: center;font-size:10px;font-family:Arial,Helvetica,sans-serif"><span style="color: #333399;"><strong>C&Aacute;M</strong><strong> ƠN Đ&Atilde; MUA SẢN PHẨM ETIAXIL VIỆT NAM</strong></span></p>';
                }else{
                    $mes = '<p style="text-align: center;font-size:10px;font-family:Arial,Helvetica,sans-serif"><span style="color: #ff0000;">M&Atilde; X&Aacute;C THỰC N&Agrave;Y Đ&Atilde; ĐƯỢC SỬ DỤNG</span><br /><span style="color: #000080;">M&Atilde; CHỈ D&Ugrave;NG ĐƯỢC 01 LẦN, NẾU CẦN HỖ TRỢ H&Atilde;Y LI&Ecirc;N HỆ ETIAXIL VIETNAM</span><br /><span style="color: #000080;">Hotline 0282 203 0808 hoặc Facebook.com/etiaxilvietnam</span></p>';
                }
                
                $arrProduct = $this->entityProducts->fetchRow($this->currentCode['product_id']);
                
                $datasProduct = ($arrProduct['datas'] != null) ? json_decode($arrProduct['datas'], true) : [];

                $arrAgent = $this->entityAgent->fetchRow($this->currentCode['agent_id']);
                $datasAgent = ($arrAgent['datas'] != null) ? json_decode($arrAgent['datas'], true) : [];
                // re update $this->currentCode
                if($this->currentCode["number_checked_qrcode"] < 1){
                    $this->currentCode["phone_id"] = $phone;
                    $this->currentCode["checked_at"] = \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent();
                }
                $this->replaceLayout([
                    "product" => array_merge($arrProduct, $datasProduct),
                    "agent" => array_merge($arrAgent, $datasAgent),
                    "message" => $mes
                    // "message" => $dataJson['message']
                ]);
                
                $view->setVariable('infoDisplay', $this->layoutScanQrcode);
                $this->layout()->setTemplate('layout_09_info');
                $view->setTemplate('application/index/layout09-info');
            }
        }

        $view->setVariable('displays', $this->displays);
        return $view;
    }

    public function layout10Action(){
        $view = new ViewModel();
        $this->layout()->setTemplate('layout_10');
        $view->setVariable('currentCode', $this->currentCode);
        //\Zend\Debug\Debug::dump($this->currentCode) ; die();
        $arrCode = $this->entityCodes->fetchRowAsQrcode($this->currentCode['qrcode'],$this->currentCode['company_id']);
        $arrProduct = $this->entityProducts->fetchRow($this->currentCode['product_id']);
        //\Zend\Debug\Debug::dump($arrCode); die();
        $form = new Layout10Form;
        $view->setVariable('form', $form);
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if($this->currentCode["id"] != $valuePost["code"]){
                $form->get('code')->setMessages($form->get('code')->getMessages() + ['code_incorrect' => 'Mã pin không chính xác!']);
                $isValid = false;
            }
            
            if($this->currentCode["number_checked_qrcode"] != 0){
                $form->get('code')->setMessages($form->get('code')->getMessages() + ['code_incorrect' => 'Mã pin đã được sử dụng!']);
                $isValid = false;
            }

            if($isValid){
                
 				// $fullname = str_replace(' ', '%20', $valuePost['fullname']);
                // $phone = \Pxt\String\ChangeString::convertPhoneVn($valuePost['phone']);
 				$message = KEYWORD_SMS . "%20" . COMPANY_ID . "%20" . $this->currentCode['id'];
 				
                $url = FULL_SERVER_NAME . "/api/index/?Command_Code=QRCODE&User_ID=" . time();
                $url .= "&Service_ID=QRCODE&Request_ID=" . time() . "&Message=" . $message;
                // $url .= "&fullname=" . $fullname;
                $dataSend = json_decode(file_get_contents($url), true);
                // $dataJson = json_decode($dataSend, true);
                // if(!isset($dataJson['status'])){
                //     die('Not found');
                // }
                // $allowCheckedLimit = isset($companyCurrent['allow_checked_limit']) ? $companyCurrent['allow_checked_limit']:100;
                // if($dataJson['status'] == 1){
                //     $mes = '<p style="text-align: center;font-size:10px;font-family:Arial,Helvetica,sans-serif"><span style="color: #ff0000;"><strong>SẢN</strong><strong> PHẨM BẠN ĐANG SỬ DỤNG L&Agrave; ETIAXIL CH&Iacute;NH H&Atilde;NG!</strong></span></p><p style="text-align: center;font-size:10px;font-family:Arial,Helvetica,sans-serif"><span style="color: #333399;"><strong>C&Aacute;M</strong><strong> ƠN Đ&Atilde; MUA SẢN PHẨM ETIAXIL VIỆT NAM</strong></span></p>';
                // }else{
                //     $mes = '<p style="text-align: center;font-size:10px;font-family:Arial,Helvetica,sans-serif"><span style="color: #ff0000;">M&Atilde; X&Aacute;C THỰC N&Agrave;Y Đ&Atilde; ĐƯỢC SỬ DỤNG</span><br /><span style="color: #000080;">M&Atilde; CHỈ D&Ugrave;NG ĐƯỢC 01 LẦN, NẾU CẦN HỖ TRỢ H&Atilde;Y LI&Ecirc;N HỆ ETIAXIL VIETNAM</span><br /><span style="color: #000080;">Hotline 0282 203 0808 hoặc Facebook.com/etiaxilvietnam</span></p>';
                // }
                
                $arrProduct = $this->entityProducts->fetchRow($this->currentCode['product_id']);
                //\Zend\Debug\Debug::dump($arrProduct); die();
                $datasProduct = ($arrProduct['datas'] != null) ? json_decode($arrProduct['datas'], true) : [];

                $arrAgent = $this->entityAgent->fetchRow($this->currentCode['agent_id']);
                $datasAgent = ($arrAgent['datas'] != null) ? json_decode($arrAgent['datas'], true) : [];
                // re update $this->currentCode
                if($this->currentCode["number_checked_qrcode"] < 1){
                    //$this->currentCode["phone_id"] = $phone;
                    $this->currentCode["checked_at"] = \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent();
                }
                $this->replaceLayout([
                    "product" => array_merge($arrProduct, $datasProduct),
                    "agent" => array_merge($arrAgent, $datasAgent),
                    "message" => $dataSend['message']
                ]);
                
                $view->setVariable('infoDisplay', $this->layoutScanQrcode);
                $this->layout()->setTemplate('layout_10_info');
                $view->setTemplate('application/index/layout10-info');
            }
        }
        $view->setVariable('displays', $this->displays);
        $view->setVariable('arrCode', $arrCode);
        $view->setVariable('arrProduct', $arrProduct);
        return $view;
    }
}
