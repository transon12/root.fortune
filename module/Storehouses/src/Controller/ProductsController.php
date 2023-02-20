<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Storehouses\Controller;

use Zend\View\Model\ViewModel;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Storehouses\Model\Products;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Storehouses\Form\Products\AddForm;
use Storehouses\Form\Products\EditForm;
use Storehouses\Form\Products\DeleteForm;
use Storehouses\Form\Products\SearchForm;
use Settings\Model\Settings;
use Codes\Model\Blocks;
use Settings\Model\Messages;
use Codes\Model\Codes;
use Promotions\Model\PromotionsProducts;
use Settings\Model\Companies;
use Zend\Json\Json;
use Zend\Filter\File\RenameUpload;

class ProductsController extends AdminCore{
    
    public $entityProducts;
    public $entitySettings;
    public $entityBlocks;
    public $entityMessages;
    public $entityCodes;
    public $entityPromotionsProducts;
    public $entityCompanies;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, Products $entityProducts, 
        Blocks $entityBlocks, Messages $entityMessages, Codes $entityCodes, PromotionsProducts $entityPromotionsProducts, Companies $entityCompanies) {
        parent::__construct($entityPxtAuthentication);
        $this->entityProducts = $entityProducts;
        $this->entitySettings = $entitySettings;
        $this->entityBlocks = $entityBlocks;
        $this->entityMessages = $entityMessages;
        $this->entityCodes = $entityCodes;
        $this->entityPromotionsProducts = $entityPromotionsProducts;
        $this->entityCompanies = $entityCompanies;
    }
    
    public function indexAction(){

        //\Zend\Debug\Debug::dump(\Admin\Service\Authentication::getUser());
        //die();

        $formSearch = new SearchForm('index', $this->sessionContainer->id);
        if($this->sessionContainer->id == '1'){
            $arrCompanies = $this->entityCompanies->fetchAllToOptions();
            $formSearch->get('company_id')->setValueOptions( [
                '' => '--- Chọn một công ty ---'] +  $arrCompanies 
            );
            //$formSearch->get('company_id')->setMessages([]);
        }
        $queries = $this->params()->fromQuery();
        
        $formSearch->setData($queries);
        // if(!isset($queries['company_id']) || $queries['company_id'] == ''){
        //     $queries['company_id'] = isset($queries['company_id']) ? $queries['company_id'] : $this->defineCompanyId;
        // }
        if($this->sessionContainer->company_id != ""){
        $queries["company_id"] = \Admin\Service\Authentication::getCompanyId();
        }
        $arrProducts = new Paginator(new ArrayAdapter( $this->entityProducts->fetchAlls($queries) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // get setting manage products
        $contentManages = $this->entitySettings->fetchManageProducts($this->defineCompanyId);
        //\Zend\Debug\Debug::dump($contentManageProducts); die();
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrProducts->setCurrentPageNumber($page);
        $arrProducts->setItemCountPerPage($perPage);
        $arrProducts->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrProducts' => $arrProducts, 
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'userId' => $this->sessionContainer->id,
            'optionCompanies' => $this->entityCompanies->fetchAllToOptions(),
            'contentManages' => $contentManages
        ]);
    }
    
    public function addAction(){
        if($this->defineCompanyId == null){
            $this->flashMessenger()->addWarningMessage('Tài khoản này không có quyền tại đây!');
            return $this->redirect()->toRoute('storehouses/products');
        }
        $settingDatas = $this->entitySettings->fetchFormProducts($this->defineCompanyId);
        $view = new ViewModel();
        $form = new AddForm('add', $settingDatas, $this->sessionContainer->id);
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $view->setVariable('valuePost', $valuePost);
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'company_id' => $this->defineCompanyId,
                    'code' => $valuePost['code'],
                    'barcode' => $valuePost['barcode'],
                    'name' => $valuePost['name'],
                    'status' => $valuePost['status'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                if($this->sessionContainer->id == '1'){
                    $dataCodes['prefix_code'] = $valuePost['prefix_code'];
                    $dataCodes['prefix_serial'] = $valuePost['prefix_serial'];
                    $dataCodes['serial_begin'] = $valuePost['serial_begin'];
                    $data['data_codes'] = json_encode($dataCodes);
                    $dataMessages['message_success'] = $valuePost['message_success'];
                    $dataMessages['message_invalid'] = $valuePost['message_invalid'];
                    $dataMessages['message_checked'] = $valuePost['message_checked'];
                    $dataMessages['message_outdate'] = $valuePost['message_outdate'];
                    $data['data_messages'] = json_encode($dataMessages, JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);
                }
                // get data
                if(!empty($settingDatas)){
                    $files = $request->getFiles()->toArray();
                    $datas = [];
                    foreach($settingDatas as $key => $item){
                        $datas[$key] = [];
                        if($item['type'] == 'File' || $item['type'] == 'Image'){
                            $datas[$key] = $valuePost[$key . '_hidden'];
                        }else{
                            $datas[$key] = $valuePost[$key];
                        }
                    }
                    $data['datas'] = Json::encode($datas);
                //\Zend\Debug\Debug::dump($datas); die();
                }
                $data['description'] = $valuePost['description'];
                $this->entityProducts->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                return $this->redirect()->toRoute('storehouses/products');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $view->setVariable('form', $form);
        $view->setVariable('settingDatas', $settingDatas);
        $view->setVariable('userId', $this->sessionContainer->id);
        return $view;
    }
    
    private function uploadFile($file = null, $param = null){
        if($file == null || $param == null) return '';
        $info = pathinfo($file[$param]['name']);
        $url = 'products/' . str_replace(' ', '-', \Pxt\String\ChangeString::deleteAccented($info['filename'])) . '_' . time() . '.' . $info['extension'];
        $fileUpload = new RenameUpload([
            'target' => FILES_UPLOAD . $url,
            'randomize' => false
        ]);
        $fileUpload->filter($file[$param]);
        return $url;
    }
    
    public function editAction(){
        // if($this->defineCompanyId == null){
        //     $this->flashMessenger()->addWarningMessage('Tài khoản này không có quyền tại đây!');
        //     return $this->redirect()->toRoute('storehouses/products');
        // }
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityProducts->fetchRowWithCompanyId($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('storehouses/products');
        }else{
            $valuePost = $valueCurrent; 
        }

        $view = new ViewModel();
        
        $settingDatas = $this->entitySettings->fetchFormProducts($this->defineCompanyId);
        // \Zend\Debug\Debug::dump($settingDatas); die();
        $form = new EditForm('edit', $settingDatas, $this->sessionContainer->id);
        //$form->setData($valuePost);
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'code' => $valuePost['code'],
                    'barcode' => $valuePost['barcode'],
                    'name' => $valuePost['name'],
                    'status' => $valuePost['status']
                ];
                if($this->sessionContainer->id == '1'){
                    $dataCodes['prefix_code'] = $valuePost['prefix_code'];
                    $dataCodes['prefix_serial'] = $valuePost['prefix_serial'];
                    $dataCodes['serial_begin'] = $valuePost['serial_begin'];
                    $data['data_codes'] = json_encode($dataCodes);
                    $dataMessages['message_success'] = $valuePost['message_success'];
                    $dataMessages['message_invalid'] = $valuePost['message_invalid'];
                    $dataMessages['message_checked'] = $valuePost['message_checked'];
                    $dataMessages['message_outdate'] = $valuePost['message_outdate'];
                    $data['data_messages'] = json_encode($dataMessages, JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);
                }else{
                    // $dataCodes['prefix_code'] = "";
                    // $dataCodes['prefix_serial'] = "";
                    // $dataCodes['serial_begin'] = "1";
                    // $data['data_codes'] = json_encode($dataCodes);
                    // $dataMessages['message_success'] = "";
                    // $dataMessages['message_invalid'] = "";
                    // $dataMessages['message_checked'] = "";
                    // $dataMessages['message_outdate'] = "";
                    // $data['data_messages'] = json_encode($dataMessages, );
                }
                $data['description'] = $valuePost['description'];
                // get data
                if(!empty($settingDatas)){
                    $files = $request->getFiles()->toArray();
                    $datas = [];
                    foreach($settingDatas as $key => $item){
                        $datas[$key] = [];
                        if($item['type'] == 'File' || $item['type'] == 'Image'){
                            $datas[$key] = $valuePost[$key . '_hidden'];
                        }else{
                            $datas[$key] = $valuePost[$key];
                        }
                    }
                    $data['datas'] = json_encode($datas);
                }
                $this->entityProducts->updateRow($id, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }else{
            if($this->sessionContainer->id == "1"){
                
                // if($valuePost['data_messages'] == null){
                //     $valuePost['data_messages']= '{}';
                // }
               // $dataMessages = Json::decode($valuePost['data_messages'], true);
                $dataMessages = json_decode($valuePost['data_messages'],true);
                
                // \Zend\Debug\Debug::dump($dataMessages);
                // die();
                $valuePost['message_success'] = isset($dataMessages['message_success']) ? $dataMessages['message_success'] : '';
                $valuePost['message_invalid'] = isset($dataMessages['message_invalid']) ? $dataMessages['message_invalid'] : '';
                $valuePost['message_checked'] = isset($dataMessages['message_checked']) ? $dataMessages['message_checked'] : '';
                $valuePost['message_outdate'] = isset($dataMessages['message_outdate']) ? $dataMessages['message_outdate'] : '';
                $dataCodes = json_decode($valuePost['data_codes'], true);
                $valuePost['prefix_code'] = isset($dataCodes['prefix_code']) ? $dataCodes['prefix_code'] : '';
                $valuePost['prefix_serial'] = isset($dataCodes['prefix_serial']) ? $dataCodes['prefix_serial'] : '';
                $valuePost['serial_begin'] = isset($dataCodes['serial_begin']) ?$dataCodes['serial_begin'] : '';
            }
            if(!empty($valuePost['datas'])){
                $datas = json_decode($valuePost['datas'], true);
                //\Zend\Debug\Debug::dump($valuePost);
                //die("ABC");
                if(!empty($datas)){
                    foreach($datas as $key => $item){
                        if($settingDatas[$key]["type"] == "Image"){
                            if(is_array($item)){
                                $valuePost[$key . "_hidden"] = "";
                            }else{
                                $valuePost[$key . "_hidden"] = $item;
                            }
                        }
                        $valuePost[$key] = $item;
                    }
                }
            }
        }
        // \Zend\Debug\Debug::dump($valuePost); die();
        $form->setData($valuePost);

        $view->setVariable('form', $form);
        $view->setVariable('settingDatas', $settingDatas);
        $view->setVariable('valuePost', $valuePost);
        $view->setVariable('userId', $this->sessionContainer->id);
        return $view;
    }
    
    public function deleteAction(){
        if($this->defineCompanyId == null){
            $this->flashMessenger()->addWarningMessage('Tài khoản này không có quyền tại đây!');
            return $this->redirect()->toRoute('storehouses/products');
        }
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityProducts->fetchRowWithCompanyId($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('products/index');
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];
//         $countBlocks = $this->entityBlocks->fetchCount(['product_id' => $id]);
//         $countMessages = $this->entityMessages->fetchCount(['product_id' => $id]);
//         $countCodes = $this->entityCodes->fetchCount(['product_id' => $id]);
//         $countPromotionsProducts = $this->entityPromotionsProducts->fetchCount(['product_id' => $id]);
//         if($countBlocks > 0){
//             $checkRelationship['blocks'] = 1;
//         }
//         if($countMessages > 0){
//             $checkRelationship['messages'] = 1;
//         }
//         if($countCodes > 0){
//             $checkRelationship['codes'] = 1;
//         }
//         if($countPromotionsProducts > 0){
//             $checkRelationship['promotions_products'] = 1;
//         }
        if($request->isPost()){
            if(!isset($checkRelationship['blocks']) && !isset($checkRelationship['messages']) && !isset($checkRelationship['codes'])){
                // delete all promotions_products
//                 $this->entityPromotionsProducts->deleteRows(['product_id' => $id]);
                // delete product
                $this->entityProducts->updateRow($id, ['status' => '-1']);
                $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            }else{
                $this->flashMessenger()->addWarningMessage('Không thể xóa, liên hệ admin để biết thêm chi tiết!');
            }
            die('success');
        }
        
        return new ViewModel([
            'form' => $form, 
            'checkRelationship' => $checkRelationship, 
            'valueCurrent' => $valueCurrent
        ]);
    }
    
    public function getProductsAsCompanyAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        $valueCurrent = [];
        $id = '0';
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $id = isset($valuePost['id']) ? $valuePost['id'] : '0';
            $valueCurrent = $this->entityCompanies->fetchRow($id);
            if(empty($valueCurrent)){
                die('error');
            }
        }else{
            die('error');
        }
        
        $optionsData = [];
        
        // get all product default
        $arrProductDefaults = $this->entitySettings->fetchConfigCodes($id);
        //\Zend\Debug\Debug::dump($arrProductDefaults); die();
        if(!empty($arrProductDefaults)){
            foreach($arrProductDefaults as $key => $item){
                $optionsData[$key] = $item['name'] . " (Ký hiệu mã: '" . $item['prefix_code'] . "'; Ký hiệu serial: '" . $item['prefix_serial'] . "'; Số serial bắt đầu: '" . $item['serial_begin'] . "')"; 
            }
        }
        
        // get all product
        $arrData = $this->entityProducts->fetchAllAsCompany($id);
        if(!empty($arrData)){
            foreach($arrData as $item){
                $itemDataCodes = json_decode($item['data_codes'], true);
                
                $optionsData[$item['id']] = $item['name'] . " (Ký hiệu mã: '" . $itemDataCodes['prefix_code'] . "'; Ký hiệu serial: '" . $itemDataCodes['prefix_serial'] . "'; Số serial bắt đầu: '" . $itemDataCodes['serial_begin'] . "')"; 
            }
        }
        //\Zend\Debug\Debug::dump($optionsData); die();
        
        return new ViewModel(['optionsData' => $optionsData]);
    }
}
