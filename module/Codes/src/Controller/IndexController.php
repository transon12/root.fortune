<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Codes\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Codes\Model\Blocks;
use Codes\Form\Index\AddForm;
use Storehouses\Model\Products;
use Codes\Model\Codes;
use Codes\Model\CodeRoots;
use Codes\Form\Index\AddPinForm;
use Codes\Model\CodeFiles;
use Settings\Model\Settings;
use Codes\Model\CodeRootsQrcode;
use Codes\Model\CodeFilesQrcode;
use Codes\Form\Index\AddQrcodeForm;
use Codes\Form\Index\SearchForm;
use Settings\Model\Companies;
use Settings\Model\CompanyConfigs;

class IndexController extends AdminCore{
    
    public $entityBlocks;
    public $entityProducts;
    public $entityCodes;
    public $entityCodeRoots;
    public $entityCodeFiles;
    public $entitySettings;
    public $entityCodeRootsQrcode;
    public $entityCodeFilesQrcode;
    public $entityCompanies;
    public $entityCompanyConfigs;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, Blocks $entityBlocks, 
        Products $entityProducts, Codes $entityCodes, CodeRoots $entityCodeRoots, CodeFiles $entityCodeFiles, 
        CodeRootsQrcode $entityCodeRootsQrcode, CodeFilesQrcode $entityCodeFilesQrcode, Companies $entityCompanies,
        CompanyConfigs $entityCompanyConfigs) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityBlocks = $entityBlocks;
        $this->entityProducts = $entityProducts;
        $this->entityCodes = $entityCodes;
        $this->entityCodeRoots = $entityCodeRoots;
        $this->entityCodeFiles = $entityCodeFiles;
        $this->entityCodeRootsQrcode = $entityCodeRootsQrcode;
        $this->entityCodeFilesQrcode = $entityCodeFilesQrcode;
        $this->entityCompanies = $entityCompanies;
        $this->entityCompanyConfigs = $entityCompanyConfigs;
    }
    
    public function indexAction(){
        if($this->sessionContainer->id != 1){
            return $this->redirect()->toRoute('admin/index');
        }
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        
        $arrBlocks = new Paginator(new ArrayAdapter( $this->entityBlocks->fetchAlls($queries) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrBlocks->setCurrentPageNumber($page);
        $arrBlocks->setItemCountPerPage($perPage);
        $arrBlocks->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrBlocks' => $arrBlocks, 
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries
        ]);
    }
    
    public function addAction(){
        if($this->sessionContainer->id != 1){
            return $this->redirect()->toRoute('admin/index');
        }
        $view = new ViewModel();
        $form = new AddForm();
        // get setting product-default
        $settingProductDefault = $this->entitySettings->fetchRow('product-default');
        $contentProductDefault = json_decode($settingProductDefault['content'], true);
        // set company_id
        $arrCompanies = $this->entityCompanies->fetchAllToOptions();
        $form->get('company_id')->setValueOptions( [
            '' => '--- Ch???n m???t c??ng ty ---'] + 
            $arrCompanies 
        );
        $form->get('products_id')->setValueOptions( 
            [ '' => '--- Ch???n m???t s???n ph???m ---' ] 
        );
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            if($valuePost['company_id'] != ""){
                $form->get('products_id')->setValueOptions( 
                    [ '' => '--- Ch???n m???t s???n ph???m ---' ] + $this->getProductsAsCompany($valuePost['company_id']) 
                );
            }
            $form->setData($valuePost);
            if($form->isValid()){
                // Check codes exist
                $numberTotal = $valuePost['number_created'];
                if($this->entityCodeRoots->countAll() < $numberTotal){
                    $this->flashMessenger()->addWarningMessage('S??? l?????ng m?? PIN l??u tr??? kh??ng ????? ????? t???o th??m!');
                }elseif($valuePost['is_qrcode'] == 1 && $this->entityCodeRootsQrcode->countAll() < $numberTotal){
                    $this->flashMessenger()->addWarningMessage('S??? l?????ng QRCode l??u tr??? kh??ng ????? ????? t???o th??m!');
                }else{
                    $arrProducts = $this->getProductsAsCompany($valuePost['company_id'], true);
                    if(!isset($arrProducts[$valuePost['products_id']])){
                        $this->flashMessenger()->addWarningMessage('C?? l???i trong qu?? tr??nh ch???n s???n ph???m!');
                    }else{
                        $currentProduct = [
                            'id_root' => isset($arrProducts[$valuePost['products_id']]['id_root']) ? $arrProducts[$valuePost['products_id']]['id_root'] : null,
                            'id' => $arrProducts[$valuePost['products_id']]['id'],
                            'prefix_code' => $arrProducts[$valuePost['products_id']]['prefix_code'],
                            'prefix_serial' => $arrProducts[$valuePost['products_id']]['prefix_serial'],
                            'serial_begin' => $arrProducts[$valuePost['products_id']]['serial_begin'],
                        ];
                        $data = [
                            'company_id' => $valuePost['company_id'],
                            'product_id' => $currentProduct['id'],
                            'name' => $valuePost['company_id'] . '-Code:' . $currentProduct['prefix_code'] . '-Serial:' . $currentProduct['prefix_serial'] . '-Begin:' . $currentProduct['serial_begin'] . '-' .date('d-m-Y-H-i-s', time()),
                            'number_serial' => $valuePost['number_serial'],
                            'number_created' => $valuePost['number_created'],
                            'is_qrcode' => $valuePost['is_qrcode'],
                            'status' => $valuePost['status'],
                            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                        ];
                        $blocksId = $this->entityBlocks->addRow($data);
                        // add code
                        $this->entityCodes->addData([
                            'company_id' => $valuePost['company_id'],
                            'blocks_id' => $blocksId,
                            'products_id' => $currentProduct['id'],
                            'number_serial' => $valuePost['number_serial'],
                            'number_created' => $valuePost['number_created'],
                            'is_qrcode' => $valuePost['is_qrcode'],
                            'serial_begin' => $currentProduct['serial_begin'],
                            'prefix_code' => $currentProduct['prefix_code'],
                            'prefix_serial' => $currentProduct['prefix_serial']
                        ]);
                        // update number_serial
                        if($currentProduct['id'] != null){
                            $dataCodes = [
                                'prefix_code' => $currentProduct['prefix_code'],
                                'prefix_serial' => $currentProduct['prefix_serial'],
                                'serial_begin' => $currentProduct['serial_begin'] + $valuePost['number_created'],
                            ];
                            $this->entityProducts->updateRow($valuePost['products_id'], ['data_codes' => json_encode($dataCodes)]);
                        }else{
                            $arrProductDefaults = [];
                            foreach($arrProducts as $key => $item){
                                if(isset($item["id_root"])){
                                    $arrProductDefaults[$key] = [
                            			"name" => $item['name'],
                            			"prefix_code" => $item['prefix_code'],
                            			"prefix_serial" => $item['prefix_serial'],
                            			"serial_begin" => $item['serial_begin']
                                    ];
                                    if($currentProduct['id_root'] === $key){
                                        $arrProductDefaults[$key]['serial_begin'] = (int)$arrProductDefaults[$key]['serial_begin'] + (int)$valuePost['number_created'];
                                    }
                                }
                            }
//                             \Zend\Debug\Debug::dump($currentProduct);
//                             \Zend\Debug\Debug::dump($arrProductDefaults);
                             //\Zend\Debug\Debug::dump($valuePost);
//                             echo json_encode($arrProductDefaults);
                            //die();
                            $this->entityCompanyConfigs->updateRow('config_codes', $valuePost['company_id'], ['content' => json_encode($arrProductDefaults)]);
                        }
                        $this->flashMessenger()->addSuccessMessage('Th??m d??? li???u th??nh c??ng!');
                        return $this->redirect()->toRoute('codes/index');
                    }
                }
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }
        $view->setVariable('form', $form);
        return $view;
    }
    
    /**
     * 
     * @param unknown $companyId
     * @param boolean $isOption: true - return option, false - return array
     */
    private function getProductsAsCompany($companyId = null, $isOption = false){
        if($companyId == null) return [];
        $companyCurrent = $this->entityCompanies->fetchRow($companyId);
        if(empty($companyCurrent)){
            return [];
        }
        
        $arrProducts = [];
        
        // get all product default
        $arrProductDefaults = $this->entitySettings->fetchConfigCodes($companyId);
        //\Zend\Debug\Debug::dump($arrProductDefaults); die();
        if(!empty($arrProductDefaults)){
            foreach($arrProductDefaults as $key => $item){
                $arrProducts[$key] = [
                    'id_root' => $key,
                    'id' => null,
                    'name' => $item['name'],
                    'prefix_code' => $item['prefix_code'],
                    'prefix_serial' => $item['prefix_serial'],
                    'serial_begin' => $item['serial_begin']
                ];
            }
        }
        
        // get all product
        $arrData = $this->entityProducts->fetchAllAsCompany($companyId);
        if(!empty($arrData)){
            foreach($arrData as $item){
                $itemDataCodes = json_decode($item['data_codes'], true);
                $arrProducts[$item['id']] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'prefix_code' => $itemDataCodes['prefix_code'],
                    'prefix_serial' => $itemDataCodes['prefix_serial'],
                    'serial_begin' => $itemDataCodes['serial_begin']
                ]; 
            }
        }
        
        if($isOption === true){
            return $arrProducts;
        }
        
        $optionProducts = [];
        if(!empty($arrProducts)){
            foreach($arrProducts as $key => $value){
                $optionProducts[$key] = $value['name'] . " (K?? hi???u m??: '" . $value['prefix_code'] . "'; K?? hi???u serial: '" . $value['prefix_serial'] . "'; S??? serial b???t ?????u: '" . $value['serial_begin'] . "')";
            }
        }
        return $optionProducts;
    }
    
    public function addPinAction(){
        if($this->sessionContainer->id != 1){
            return $this->redirect()->toRoute('admin/index');
        }
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new AddPinForm($request->getRequestUri());

        if($request->isPost()){
            // get name file random
            $currentCodeFile = $this->entityCodeFiles->fetchRowRandom();
            if(empty($currentCodeFile)){
                $this->flashMessenger()->addWarningMessage('???? h???t file d??? li???u g???c!');
            }else{
                $nameFile = APPLICATION_PATH . '/public/data/pin/' . $currentCodeFile['name'];
                $fp = @fopen($nameFile, "r");
                if (!$fp){
                    $this->flashMessenger()->addWarningMessage('M??? file kh??ng th??nh c??ng!');
                }else{
                    while(!feof($fp)){
                        $strRow = fgets($fp);
                        if(strlen($strRow) > 2){
                            $arrCodes = explode(" ", $strRow);
                            $this->entityCodeRoots->addRows($this->strInsertCodeRoots($arrCodes));
                        }
                    }
                    @fclose($nameFile);
                    // delete code file
                    $this->entityCodeFiles->updateRow($currentCodeFile['name'], ['status' => 0]);
                    $this->flashMessenger()->addSuccessMessage('Th??m m?? PIN th??nh c??ng!');
                }
            }
            die('success');
        }
        
        return new ViewModel(['form' => $form]);
    }
    
    public function addQrcodeAction(){
        if($this->sessionContainer->id != 1){
            return $this->redirect()->toRoute('admin/index');
        }
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new AddQrcodeForm($request->getRequestUri());

        if($request->isPost()){
            // get name file random
            $currentCodeFileQrcode = $this->entityCodeFilesQrcode->fetchRowRandom();
            if(empty($currentCodeFileQrcode)){
                $this->flashMessenger()->addWarningMessage('???? h???t file d??? li???u g???c!');
            }else{
                $nameFile = APPLICATION_PATH . '/public/data/qrcode/' . $currentCodeFileQrcode['name'];
                $fp = @fopen($nameFile, "r");
                if (!$fp){
                    $this->flashMessenger()->addWarningMessage('M??? file kh??ng th??nh c??ng!');
                }else{
                    while(!feof($fp)){
                        $strRow = fgets($fp);
                        if(strlen($strRow) > 2){
                            $arrCodes = explode(" ", $strRow);
                            $this->entityCodeRootsQrcode->addRows($this->strInsertCodeRootsQrcode($arrCodes));
                        }
                    }
                    @fclose($nameFile);
                    // delete code file
                    $this->entityCodeFilesQrcode->updateRow($currentCodeFileQrcode['name'], ['status' => 0]);
                    $this->flashMessenger()->addSuccessMessage('Th??m QRCode th??nh c??ng!');
                }
            }
            die('success');
        }
        
        return new ViewModel(['form' => $form]);
    }
    
    private function strInsertCodeRoots($arrCodes){
        if(empty($arrCodes)) return '';
        $str = 'insert into code_roots (`random`, `id`) values ';
        $i = 0;
        foreach($arrCodes as $item){
            $random = rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);
            if($item != '' && strlen($item) > 2){
                if($i != 0){
                    $str .= ", ";
                }
                $str .= "('" . $random . "', '" . $item . "')";
            }
            $i++;
        }
        $str .= ";";
        return $str;
    }
    
    private function strInsertCodeRootsQrcode($arrCodes){
        if(empty($arrCodes)) return '';
        $str = 'insert into code_roots_qrcode (`random`, `id`) values ';
        $i = 0;
        foreach($arrCodes as $item){
            $random = rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);
            if($item != '' && strlen($item) > 2){
                if($i != 0){
                    $str .= ", ";
                }
                $str .= "('" . $random . "', '" . $item . "')";
            }
            $i++;
        }
        $str .= ";";
        return $str;
    }
}
