<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Storehouses\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Storehouses\Model\Agents;
use Storehouses\Form\Agents\AddForm;
use Storehouses\Form\Agents\EditForm;
use Storehouses\Form\Agents\DeleteForm;
use Storehouses\Form\Agents\ExportsForm;
use Storehouses\Form\Agents\SearchForm;
use Settings\Model\Settings;
use Settings\Model\Phones;
use Codes\Model\Codes;
use Storehouses\Model\Products;
use Storehouses\Model\Storehouses;
use Zend\Debug\Debug;
use Admin\Model\Users;
use Settings\Model\Countries;
use Settings\Model\Cities;
use Settings\Model\Districts;
use Settings\Model\Wards;
use Settings\Model\Companies;
use Zend\Filter\File\RenameUpload;
use Storehouses\Form\Agents\AddFileForm;
use Storehouses\Form\Agents\ExportFileForm;

class AgentsController extends AdminCore{
    
    public $entityAgents;
    public $entitySettings;
    public $entityPhones;
    public $entityCodes;
    public $entityProducts;
    public $entityStorehouses;
    public $entityUsers;
    public $entityCountries;
    public $entityCities;
    public $entityDistricts;
    public $entityWards;
    public $entityCompanies;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, 
        Agents $entityAgents, Phones $entityPhones, Codes $entityCodes, Products $entityProducts, Storehouses $entityStorehouses,
        Users $entityUsers, Countries $entityCountries, Cities $entityCities, Districts $entityDistricts, Wards $entityWards,
        Companies $entityCompanies) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityAgents = $entityAgents;
        $this->entityPhones = $entityPhones;
        $this->entityCodes = $entityCodes;
        $this->entityProducts = $entityProducts;
        $this->entityStorehouses = $entityStorehouses;
        $this->entityUsers = $entityUsers;
        $this->entityCountries = $entityCountries;
        $this->entityCities = $entityCities;
        $this->entityDistricts = $entityDistricts;
        $this->entityWards = $entityWards;
        $this->entityCompanies = $entityCompanies;
    }
    
    public function indexAction(){
        $formSearch = new SearchForm('index', $this->sessionContainer->id);
        if($this->sessionContainer->id == '1'){
            $arrCompanies = $this->entityCompanies->fetchAllToOptions();
            $formSearch->get('company_id')->setValueOptions( [
                '' => '--- Ch???n m???t c??ng ty ---'] + 
                $arrCompanies 
            );
        }
        $optionUsers = $this->entityUsers->fetchAllOptions(["company_id" => $this->defineCompanyId]);
        $formSearch->get('user_id')->setValueOptions( ['' => '--- Ch???n m???t t??i kho???n t???o ---' ] + $optionUsers );
        $queries = $this->params()->fromQuery();
        // reset company_id if empty
        if(!isset($queries['company_id']) || $queries['company_id'] == ''){
            $queries['company_id'] = isset($queries['company_id']) ? $queries['company_id'] : $this->defineCompanyId;
        }
        $formSearch->setData($queries);
        // check agent
        $queries['view_all_agent'] = $this->sessionContainer->configs['view_all_agent'];
        // set user id
        if((int)$queries['view_all_agent'] != 1){
            $queries['user_id'] = $this->sessionContainer->id;
        }
        $arrAgents = new Paginator(new ArrayAdapter( $this->entityAgents->fetchAlls($queries) ));
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // get setting manage products
        $contentManages = $this->entitySettings->fetchManageAgents($this->defineCompanyId);
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrAgents->setCurrentPageNumber($page);
        $arrAgents->setItemCountPerPage($perPage);
        $arrAgents->setPageRange($contentPaginator['page_range']);
        //\Zend\Debug\Debug::dump($this->sessionContainer->configs); die();
        return new ViewModel([
            'arrAgents' => $arrAgents, 
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'userConfigs' => $this->sessionContainer->configs,
            'userId' => $this->sessionContainer->id,
            'optionCompanies' => $this->entityCompanies->fetchAllToOptions(),
            'contentManages' => $contentManages,
            'optionUsers' => $optionUsers
        ]);
    }
    
    public function addAction(){
        if($this->defineCompanyId == null){
            $this->flashMessenger()->addWarningMessage('T??i kho???n n??y kh??ng c?? quy???n t???i ????y!');
            die('success');
        }
        $settingDatas = $this->entitySettings->fetchFormAgents($this->defineCompanyId);
        $view = new ViewModel();
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new AddForm($request->getRequestUri(), $settingDatas, $this->sessionContainer->id);
        // add data countries
        // $optionCountries = $this->entityCountries->fetchAllOptions();
        // $form->get('country_id')->setValueOptions( ['' => '--- Ch???n m???t ?????t n?????c ---' ] + $optionCountries );
        // // add data default cities, districts, wards
        // $form->get('city_id')->setValueOptions( ['' => '--- Ch???n m???t t???nh (th??nh ph???) ---' ] );
        // $form->get('district_id')->setValueOptions( ['' => '--- Ch???n m???t qu???n (huy???n) ---' ] );
        // $form->get('ward_id')->setValueOptions( ['' => '--- Ch???n m???t ph?????ng (x??) ---' ] );
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            // load data cities
            // if(isset($valuePost['country_id']) && $valuePost['country_id'] != ''){
            //     $optionCities = $this->entityCities->fetchAllOptions(['country_id' => $valuePost['country_id']]);
            //     $form->get('city_id')->setValueOptions( ['0' => '--- Ch???n m???t t???nh (th??nh ph???) ---' ] + $optionCities );
            // }
            // // load data districts
            // if(isset($valuePost['city_id']) && $valuePost['city_id'] != ''){
            //     $optionDistricts = $this->entityDistricts->fetchAllOptions(['city_id' => $valuePost['city_id']]);
            //     $form->get('district_id')->setValueOptions( ['0' => '--- Ch???n m???t qu???n (huy???n) ---' ] + $optionDistricts );
            // }
            // // load data wards
            // if(isset($valuePost['district_id']) && $valuePost['district_id'] != ''){
            //     $optionWards = $this->entityWards->fetchAllOptions(['district_id' => $valuePost['district_id']]);
            //     $form->get('ward_id')->setValueOptions( ['0' => '--- Ch???n m???t ph?????ng (x??) ---' ] + $optionWards );
            // }
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'company_id' => \Admin\Service\Authentication::getCompanyId(),
                    'user_id' => $this->sessionContainer->id,
                    'name' => $valuePost['name'],
                    'code' => $valuePost['code'],
                    'address' => $valuePost['address'],
                    // 'ward_id' => $valuePost['ward_id'],
                    'status' => $valuePost['status'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                // get data
                if(!empty($settingDatas)){
                    $files = $request->getFiles()->toArray();
                    $datas = [];
                    foreach($settingDatas as $key => $item){
                        $datas[$key] = [];
                        if($item['type'] == 'File' || $item['type'] == 'Image'){
                            if($files[$key]['name'] != ''){
                                $datas[$key] = $this->uploadFile($files, $key);
                            }
                        }else{
                            $datas[$key] = $valuePost[$key];
                        }
                    }
                    $data['datas'] = json_encode($datas);
                }
                $this->entityAgents->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Th??m d??? li???u th??nh c??ng!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }

        $view->setVariable('form', $form);
        $view->setVariable('settingDatas', $settingDatas);
        $view->setVariable('userId', $this->sessionContainer->id);
        return $view;
    }
    
    public function addFileAction(){
        if($this->defineCompanyId == null){
            $this->flashMessenger()->addWarningMessage('T??i kho???n n??y kh??ng c?? quy???n t???i ????y!');
            die('success');
        }
        $view = new ViewModel();
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new AddFileForm($request->getRequestUri());
        
        if($request->isPost()){
            $valuePost = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($valuePost);
            if($form->isValid()){
                    $file = $request->getFiles()->toArray();
                    //upload background
                    if($file['file']['name'] != ''){
                        $url = $this->uploadFile($file, 'file');
                        //echo $url; die();
                        if($this->importAgents($url)){
                            $this->flashMessenger()->addSuccessMessage('Th??m d??? li???u th??nh c??ng!');
                            die('success');
                        }else{
                            $this->flashMessenger()->addWarningMessage('File c?? v???n ?????, ????? ngh??? ki???m tra l???i!');
                        }
                    }
//                 $data = [
//                     'company_id' => COMPANY_ID,
//                     'user_id' => $this->sessionContainer->id,
//                     'name' => $valuePost['name'],
//                     'code' => $valuePost['code'],
//                     'address' => $valuePost['address'],
//                     'ward_id' => $valuePost['ward_id'],
//                     'status' => $valuePost['status'],
//                     'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
//                 ];
//                 $this->entityAgents->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Th??m d??? li???u th??nh c??ng!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }

        $view->setVariable('form', $form);
        $view->setVariable('userId', $this->sessionContainer->id);
        return $view;
    }
	
	/**
	 * import data
	 */
	function importAgents($url = ""){

		set_time_limit(7200);
		ini_set('memory_limit', '4096M');
		if($url == ""){
		    return false;
		}
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('C?? l???i trong qu?? tr??nh xu???t d??? li???u, ????? ngh??? li??n h??? Admin!');
            return $this->redirect()->toRoute('storehouses/agents');
        }
		// Load n???i dung file l??n ????? x??? l??
		try {
			$objPHPExcel = \PHPExcel_IOFactory::load(FILES_UPLOAD . $url);
		} catch(Exception $e) {
			die("L???i load th?? vi???n '".pathinfo($url, PATHINFO_BASENAME) . "': " . $e->getMessage());
		}
		
		// L???y d??? li???u t???i sheet m???c ?????nh
		$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

		//die('abc');
		$n = 1;
		if(!empty($sheetData)){
            $dataSheet = [];
            $codes = "";
			foreach($sheetData as $itemSheet){
				if($n >= 3){
                    $code = trim($itemSheet["B"]);
                    $code = str_replace("'", "\'", $code);
                    $code = str_replace("\"", "\\\"", $code);
                    $name = trim($itemSheet["C"]);
                    $name = str_replace("'", "\'", $name);
                    $name = str_replace("\"", "\\\"", $name);
                    $dataSheet[$code]["code"] = $code;
                    $dataSheet[$code]["name"] = $name;
                    if($n != 3){
                        $codes .= ", ";
                    }
                    $codes .= "('" . $code . "')";
				}
				$n++;
			}
            // \Zend\Debug\Debug::dump($dataSheet);
            // die();
            $currentAgents = $this->entityAgents->fetchRowCodes($codes);
            $optionAgents = [];
            if(!empty($currentAgents)){
                foreach($currentAgents as $item){
                    $optionAgents[$item["code"]] = $item["name"];
                }
            }
            // \Zend\Debug\Debug::dump($optionAgents);
            // die();
            $strInserts = "insert into agents (`company_id`, `user_id`, `code`, `name`, `created_at`) values ";
            $strUpdates = "";
            $i = 0;
			foreach($dataSheet as $key => $item){
                if(strlen($item['code']) > 0 && strlen($item['name']) > 0){
                    $code = $key;
                    $name = $item["name"];
                    $companyId      = \Admin\Service\Authentication::getCompanyId();
                    $userId         = \Admin\Service\Authentication::getId();
                    $createdAt      = \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent();
                    if(!isset($optionAgents[$code])){
                        // $dataAgent = [];
                        // $dataAgent = [
                        //     'company_id'    => $this->defineCompanyId,
                        //     'user_id'       => $this->sessionContainer->id,
                        //     'code'          => $code,
                        //     'name'          => $name,
                        //     'created_at'    => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                        // ];
                        // $this->entityAgents->addRow($dataAgent);
                        if($i != 0){
                            $strInserts .= ", ";
                        }
                        $strInserts .= "('" . $companyId . "', '" . $userId . "', '" . $code . "', '" . $name . "', '" . $createdAt . "')";
                    }else{
                        // $dataAgent = [];
                        // $dataAgent = [
                        //     'name' => $name,
                        // ];
                        // $this->entityAgents->updateRow($currentAgent['id'], $dataAgent);
                        if($name != $optionAgents[$code]){
                            $strUpdates .= "update agents set `name` = '" . $name . "' where company_id = '" . $companyId . "' and `code` = '" . $code . "'; ";
                        }
                    }
                    $i++;
                }
            }
            $strInserts .= ";";
            // echo $strInserts . "<br /><br />" . $strUpdates; die();
            $this->entityAgents->runSql($strInserts);
            $this->entityAgents->runSql($strUpdates);
		}
		//die('abc');
		return true;
	}

    public function addExcelSampleAction(){
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('C?? l???i trong qu?? tr??nh xu???t d??? li???u, ????? ngh??? li??n h??? Admin!');
            return $this->redirect()->toRoute('storehouses/agents');
        }
        // ?????t t??n file
        $path = "Mau-nhap-dai-ly.xlsx";
        	
        /* T???o m???i m???t ?????i t?????ng PHPExcel */
        $objPHPExcel = new \PHPExcel();
        /* C??i ?????t Properties */
        $objPHPExcel->getProperties()->setCreator("Pxt Creator")
        ->setLastModifiedBy("Pxt Modified")
        ->setTitle("Pxt Title")
        ->setSubject("Pxt Subject")
        ->setDescription("Pxt Description")
        ->setKeywords("Pxt Keywords")
        ->setCategory("Pxt Category");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')
        ->setSize(12); /* C??i ?????t font cho c??? file */
        	
        /* C??i ?????t chi???u r???ng cho t???ng ?? */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
        	
        /* Ch???nh d??ng ?????u ti??n */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:D1'); /* Nh??m c???t */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("M???u nh???p ?????i l??");
        $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_DARKGREEN ) );
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'STT')
        ->setCellValue('B2', 'M?? ?????i l??')
        ->setCellValue('C2', 'T??n ?????i l??');
        $objPHPExcel->getActiveSheet()->getStyle('A2:C2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:C2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:C2')->getFont()->setBold(true);
        
        // Redirect output to a client???s web browser (Excel2007)
        ob_end_clean(); /* Xo?? (hay lo???i b???) b???t k??? kho???ng tr???ng trong c???p l???nh n??y*/
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$path.'"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    
        ob_end_clean();
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        return true;
    }
    
    public function exportFileAction(){
        if($this->defineCompanyId == null){
            $this->flashMessenger()->addWarningMessage('T??i kho???n n??y kh??ng c?? quy???n t???i ????y!');
            die('success');
        }
        $view = new ViewModel();
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new ExportFileForm($request->getRequestUri());
        
        if($request->isPost()){
            $valuePost = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($valuePost);
            if($form->isValid()){
                    $file = $request->getFiles()->toArray();
                    //upload background
                    if($file['file']['name'] != ''){
                        $url = $this->uploadFile($file, 'file');
                        //echo $url; die();
                        if($this->importExports($url)){
                            $this->flashMessenger()->addSuccessMessage('Th??m d??? li???u th??nh c??ng!');
                            die('success');
                        }else{
                            $this->flashMessenger()->addWarningMessage('File c?? v???n ?????, ????? ngh??? ki???m tra l???i!');
                        }
                    }
//                 $data = [
//                     'company_id' => COMPANY_ID,
//                     'user_id' => $this->sessionContainer->id,
//                     'name' => $valuePost['name'],
//                     'code' => $valuePost['code'],
//                     'address' => $valuePost['address'],
//                     'ward_id' => $valuePost['ward_id'],
//                     'status' => $valuePost['status'],
//                     'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
//                 ];
//                 $this->entityAgents->addRow($data);
                $this->flashMessenger()->addSuccessMessage('C???p nh???t d??? li???u th??nh c??ng!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }

        $view->setVariable('form', $form);
        $view->setVariable('userId', $this->sessionContainer->id);
        return $view;
    }
	
	/**
	 * import data
	 */
	function importExports($url = ""){
		set_time_limit(7200);
		ini_set('memory_limit', '4096M');
		if($url == ""){
		    return false;
		}
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('C?? l???i trong qu?? tr??nh xu???t d??? li???u, ????? ngh??? li??n h??? Admin!');
            return $this->redirect()->toRoute('storehouses/agents');
        }
		// Load n???i dung file l??n ????? x??? l??
		try {
			$objPHPExcel = \PHPExcel_IOFactory::load(FILES_UPLOAD . $url);
		} catch(Exception $e) {
			die("L???i load th?? vi???n '".pathinfo($url, PATHINFO_BASENAME) . "': " . $e->getMessage());
		}
		
		// L???y d??? li???u t???i sheet m???c ?????nh
		$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        $companyId = \Admin\Service\Authentication::getCompanyId();
		// die('abc');
		if(!empty($sheetData)){
            $dataSheet = [];
            $n = 1;
            $arrCodeAgents  = [];
            $arrQrcodes     = [];
            $arrSerials     = [];
			foreach($sheetData as $itemSheet){
                if($n >= 5){
                    $codeAgent  = trim( str_replace( "\"", "", str_replace( "'", "", $itemSheet["A"] ) ) );
                    $exportedAt = trim( str_replace( "\"", "", str_replace( "'", "", $itemSheet["B"] ) ) );
                    if(strlen($exportedAt) < 1){
                        $exportedAt = \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent();
                    }else{
                        $exportedAt = date_create_from_format('d/m/Y H:i:s', $exportedAt);
                        $exportedAt = date_format($exportedAt, 'Y-m-d H:i:s');
                    }
                    $qrcode     = trim( str_replace( "\"", "", str_replace( "'", "", $itemSheet["C"] ) ) );
                    $link       = "";
                    if($qrcode != ""){
                        $explodeQrcode = explode("=", $qrcode);
                        $qrcode = (isset($explodeQrcode[1])) ? $explodeQrcode[1] : $qrcode;
                        $link = $explodeQrcode[0];
                    }
                    $serial     = ($qrcode == "") ?trim( str_replace( "\"", "", str_replace( "'", "", $itemSheet["D"] ) ) ) : "";
                    $dataSheet[$n] = [
                        "codeAgent"     => $codeAgent,
                        "exportedAt"    => $exportedAt,
                        "qrcode"        => $qrcode,
                        "link"          => $link,
                        "serial"        => $serial
                    ];
                    if($codeAgent != ""){
                        $arrCodeAgents[$codeAgent]  = $codeAgent;
                    }
                    if($qrcode != ""){
                        $arrQrcodes[$qrcode]        = $qrcode;
                    }
                    if($serial != ""){
                        $arrSerials[$serial]        = $serial;
                    }
                }
				$n++;
			}
            $strCodeAgents  = "'" . implode("', '", $arrCodeAgents) . "'";
            $strQrcodes     = "'" . implode("', '", $arrQrcodes) . "'";
            $strSerials     = "'" . implode("', '", $arrSerials) . "'";

            $arrAgents = $this->entityAgents->fetchAllToOptionsByCode($strCodeAgents);
            // echo "Agents: <Br />";
            // \Zend\Debug\Debug::dump($arrAgents);
            $arrCodesQrcode = $this->entityCodes->fetchAllToOptionsAsQrcodes($strQrcodes);
            // echo "Code QRCode: <Br />";
            // \Zend\Debug\Debug::dump($arrCodesQrcode);
            $arrCodesSerial = $this->entityCodes->fetchAllToOptionsAsSerials($strSerials);
            // echo "Code Serial: <Br />";
            // \Zend\Debug\Debug::dump($arrCodesSerial);
            // echo strlen($strCodeAgents) . " - " . $strCodeAgents . "<br />";
            // echo strlen($strQrcodes) . " - " . $strQrcodes . "<br />";
            // echo strlen($strSerials) . " - " . $strSerials . "<br />";
            // \Zend\Debug\Debug::dump($dataSheet);
            // die();
            $errors = "";
            $strUpdates = "";
            foreach($dataSheet as $key => $item){
                $error = "";
                if(strlen($item["codeAgent"]) < 1){
                    if($item["qrcode"] != ""){
                        $strUpdates .= "update codes_" . strtolower($companyId) . " set agent_id = null, exported_at = null where `qrcode` = '" . $item["qrcode"] . "'; ";
                    }else{
                        $strUpdates .= "update codes_" . strtolower($companyId) . " set agent_id = null, exported_at = null where `serial` = '" . $item["serial"] . "'; ";
                    }
                }else{
                    if(!isset($arrAgents[$item["codeAgent"]])){
                        $error .= " M?? ?????i l?? '" . $item["codeAgent"] . "' kh??ng t??m th???y;";
                    }
                    if($item["qrcode"] != ""){
                        if(!isset($arrCodesQrcode[$item["qrcode"]])){
                            $error .= " QRCode '" . $item["link"] . "=" . $item["qrcode"] . "' kh??ng t??m th???y;";
                        }else{
                            if(strlen($arrCodesQrcode[$item["qrcode"]]["imported_at"]) < 1){
                                $error .= " QRCode '" . $item["link"] . "=" . $item["qrcode"] . "' ch??a ???????c nh???p kho;";
                            }else{
                                if(strlen($arrCodesQrcode[$item["qrcode"]]["exported_at"]) > 0){
                                    $error .= " QRCode '" . $item["link"] . "=" . $item["qrcode"] . "' ???? ???????c xu???t kho tr?????c ????;";
                                }
                            }
                        }
                    }else{
                        if(!isset($arrCodesSerial[$item["serial"]])){
                            $error .= " Serial '" . $item["serial"] . "' kh??ng t??m th???y;";
                        }else{
                            if(strlen($arrCodesSerial[$item["serial"]]["imported_at"]) < 1){
                                $error .= " Serial '" . $item["serial"] . "' ch??a ???????c nh???p kho;";
                            }else{
                                if(strlen($arrCodesSerial[$item["serial"]]["exported_at"]) > 0){
                                    $error .= " Serial '" . $item["serial"] . "' ???? ???????c xu???t kho tr?????c ????;";
                                }
                            }
                        }
                    }
                    if($error != ""){
                        $error = "<strong>L???i d??ng (th??m) " . $key . ":</strong>" . $error;
                        $error .= "<br />";
                    }else{
                        if($item["qrcode"] != ""){
                            $strUpdates .= "update codes_" . strtolower($companyId) . " set agent_id = '" . $arrAgents[$item["codeAgent"]] . "', exported_at = '" . $item["exportedAt"] . "' where `qrcode` = '" . $item["qrcode"] . "'; ";
                        }else{
                            $strUpdates .= "update codes_" . strtolower($companyId) . " set agent_id = '" . $arrAgents[$item["codeAgent"]] . "', exported_at = '" . $item["exportedAt"] . "' where `serial` = '" . $item["serial"] . "'; ";
                        }
                    }
                }
                $errors .= $error;
            }
            if($errors != ""){
                echo $errors;
                die();
            }else{
                // echo $strUpdates; die();
                $this->entityCodes->runSql($strUpdates);
            }
		}
		//die('abc');
		return true;
	}

    public function exportExcelSampleAction(){
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('C?? l???i trong qu?? tr??nh xu???t d??? li???u, ????? ngh??? li??n h??? Admin!');
            return $this->redirect()->toRoute('storehouses/agents');
        }
        // ?????t t??n file
        $path = "Mau-nhap-xuat-kho-cho-dai-ly.xlsx";
        	
        /* T???o m???i m???t ?????i t?????ng PHPExcel */
        $objPHPExcel = new \PHPExcel();
        /* C??i ?????t Properties */
        $objPHPExcel->getProperties()->setCreator("Pxt Creator")
        ->setLastModifiedBy("Pxt Modified")
        ->setTitle("Pxt Title")
        ->setSubject("Pxt Subject")
        ->setDescription("Pxt Description")
        ->setKeywords("Pxt Keywords")
        ->setCategory("Pxt Category");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')
        ->setSize(12); /* C??i ?????t font cho c??? file */
        	
        /* C??i ?????t chi???u r???ng cho t???ng ?? */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
        	
        /* Ch???nh d??ng ?????u ti??n */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:D1'); /* Nh??m c???t */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("M???u nh???p 'xu???t kho cho ?????i l??'");
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_DARKGREEN ) );
        /* Ch???nh d??ng 2 */
        $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(150);
        $note = "Ghi ch??\n";
        $note .= "1. QRCode ho???c Serial ??? ????y n???u c?? tr??ng v???i d??? li???u tr?????c ???? (kh??ng n???m trong file n??y) s??? kh??ng ???????c c???p nh???t\n";
        $note .= "2. D??? li???u ph???i ghi ????ng ?????nh d???ng gi???ng c???u tr??c v?? d??? ??? d??ng 4 (n???u c??) (Quan tr???ng: 'Ng??y nh???p', 'Serial' (n???u c??))\n";
        $note .= "3. Serial n???u c?? s??? 0 ??? ?????u v?? ng??y th??ng ph???i b??? sung d???u ' tr?????c ????? tr??nh sai l???ch th??ng tin\n";
        $note .= "4. M?? ?????i l?? ph???i ghi ????ng nh?? ???? c???p nh???t l??n trang qu???n l??\n";
        $note .= "5. D??? li???u b???t ?????u l???y t??? d??ng 5\n";
        $note .= "6. C???t 'QRCode', 'Serial' ch??? ???????c nh???p d??? li???u 1 trong 2 c???t, s??? ??u ti??n c???t QRCode n???u nh???p c??? 2\n";
        $note .= "7. N???u mu???n x??a d??? li???u th?? c???t 'M?? ?????i l??' b??? tr???ng\n";
        $note .= "8. N???u v???a mu???n x??a, v???a mu???n xu???t l???i c??ng 'QRCode' ho???c 'Serial' tr??n c??ng file n??y th?? d??ng x??a ph???i ?????t tr?????c d??ng mu???n th??m\n";
        $objPHPExcel->getActiveSheet()->mergeCells('A2:D2'); /* Nh??m c???t */
        $objPHPExcel->getActiveSheet()->getCell('A2')->setValue($note);
        $objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_TOP);
        $objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getFont()->setSize(12);
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A3', 'M?? ?????i l??')
        ->setCellValue('B3', 'Ng??y nh???p (dd/mm/YY H:i:s)')
        ->setCellValue('C3', 'QRCode')
        ->setCellValue('D3', 'Serial (n???u c?? kh??ng nh???p QRCode)');
        $objPHPExcel->getActiveSheet()->getStyle('A3:D3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A3:D3')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A3:D3')->getFont()->setBold(true);
        // set atribute row 3
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A4', 'CODEDAILY')
        ->setCellValue('B4', '\'20/12/2020 12:59:59')
        ->setCellValue('C4', 'QRCode')
        ->setCellValue('D4', '\'0000001');
        $objPHPExcel->getActiveSheet()->getStyle('A4:D4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A4:D4')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A4:D4')->getFont()->setSize(8)->setItalic(true);
        
        // Redirect output to a client???s web browser (Excel2007)
        ob_end_clean(); /* Xo?? (hay lo???i b???) b???t k??? kho???ng tr???ng trong c???p l???nh n??y*/
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$path.'"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    
        ob_end_clean();
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        return true;
    }
    
    private function uploadFile($file = null, $param = null){
        if($file == null || $param == null) return '';
        $info = pathinfo($file[$param]['name']);
        $url = 'agents/' . str_replace(' ', '-', \Pxt\String\ChangeString::deleteAccented($info['filename'])) . '_' . time() . '.' . $info['extension'];
        $fileUpload = new RenameUpload([
            'target' => FILES_UPLOAD . $url,
            'randomize' => false
        ]);
        $fileUpload->filter($file[$param]);
        return $url;
    }
    
    public function editAction(){
        if($this->defineCompanyId == null){
            $this->flashMessenger()->addWarningMessage('T??i kho???n n??y kh??ng c?? quy???n t???i ????y!');
            die('success');
        }
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityAgents->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('agents/index');
        }else{
            $valuePost = $valueCurrent; 
        }
        
        $settingDatas = $this->entitySettings->fetchFormAgents($this->defineCompanyId);
        $form = new EditForm($request->getRequestUri(), $settingDatas, $this->sessionContainer->id);
        // $optionCountries = $this->entityCountries->fetchAllOptions();
        // $form->get('country_id')->setValueOptions( ['' => '--- Ch???n m???t ?????t n?????c ---' ] + $optionCountries );
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            // load data cities
            // if(isset($valuePost['country_id']) && $valuePost['country_id'] != ''){
            //     $optionCities = $this->entityCities->fetchAllOptions(['country_id' => $valuePost['country_id']]);
            //     $form->get('city_id')->setValueOptions( ['0' => '--- Ch???n m???t t???nh (th??nh ph???) ---' ] + $optionCities );
            // }
            // // load data districts
            // if(isset($valuePost['city_id']) && $valuePost['city_id'] != ''){
            //     $optionDistricts = $this->entityDistricts->fetchAllOptions(['city_id' => $valuePost['city_id']]);
            //     $form->get('district_id')->setValueOptions( ['0' => '--- Ch???n m???t qu???n (huy???n) ---' ] + $optionDistricts );
            // }
            // // load data wards
            // if(isset($valuePost['district_id']) && $valuePost['district_id'] != ''){
            //     $optionWards = $this->entityWards->fetchAllOptions(['district_id' => $valuePost['district_id']]);
            //     $form->get('ward_id')->setValueOptions( ['0' => '--- Ch???n m???t ph?????ng (x??) ---' ] + $optionWards );
            // }
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'name'      => $valuePost['name'],
                    'code'      => $valuePost['code'],
                    // 'ward_id'   => $valuePost['ward_id'],
                    'status'    => $valuePost['status'],
                ];
                // get data
                if(!empty($settingDatas)){
                    $files = $request->getFiles()->toArray();
                    $datas = [];
                    foreach($settingDatas as $key => $item){
                        $datas[$key] = [];
                        if($item['type'] == 'File' || $item['type'] == 'Image'){
                            if($files[$key]['name'] != ''){
                                $valuePost[$key] = $datas[$key] = $this->uploadFile($files, $key);
                            }else{
                                $dataCurrent = json_decode($valueCurrent['datas'], true);
                                $valuePost[$key] = $datas[$key] = $dataCurrent[$key];
                            }
                        }else{
                            $datas[$key] = $valuePost[$key];
                        }
                    }
                    $data['datas'] = json_encode($datas);
                }
                $this->entityAgents->updateRow($id, $data);
                $this->flashMessenger()->addSuccessMessage('S???a d??? li???u th??nh c??ng!');
                die('success');
                //return $this->redirect()->toRoute('admin/users');
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }else{
            // if(isset($valuePost['ward_id']) && $valuePost['ward_id'] != ''){
            //     $valuePost['ward_id'] = $valuePost['ward_id'];
            //     $ward = $this->entityWards->fetchRow($valuePost['ward_id']);
            //     if(isset($ward['district_id']) && $ward['district_id'] != '' && $ward['district_id'] != null){
            //         $optionWards = $this->entityWards->fetchAllOptions(['district_id' => $ward['district_id']]);
            //         $form->get('ward_id')->setValueOptions( ['0' => '--- Ch???n m???t ph?????ng (x??) ---' ] + $optionWards );
            //         $district = $this->entityDistricts->fetchRow($ward['district_id']);
            //         $valuePost['district_id'] = $ward['district_id'];
            //     }
            //     if(isset($district['city_id']) && $district['city_id'] != '' && $district['city_id'] != null){
            //         $optionDistricts = $this->entityDistricts->fetchAllOptions(['city_id' => $district['city_id']]);
            //         $form->get('district_id')->setValueOptions( ['0' => '--- Ch???n m???t qu???n (huy???n) ---' ] + $optionDistricts );
            //         $city = $this->entityCities->fetchRow($district['city_id']);
            //         $valuePost['city_id'] = $district['city_id'];
            //     }
            //     if(isset($city['country_id']) && $city['country_id'] != '' && $city['country_id'] != null){
            //         $optionCities = $this->entityCities->fetchAllOptions(['country_id' => $city['country_id']]);
            //         $form->get('city_id')->setValueOptions( ['0' => '--- Ch???n m???t t???nh (th??nh ph???) ---' ] + $optionCities );
            //         $country = $this->entityCountries->fetchRow($city['country_id']);
            //         $valuePost['country_id'] = $city['country_id'];
            //     }
            // }
            if(!empty($valuePost['datas'])){
                $datas = json_decode($valuePost['datas'], true);
                if(!empty($datas)){
                    foreach($datas as $key => $item){
                        $valuePost[$key] = $item;
                    }
                }
            }
        }
        $form->setData($valuePost);
        
               // \Zend\Debug\Debug::dump($valueCurrent); die();
        return new ViewModel([
            'form' => $form,
            'settingDatas' => $settingDatas
        ]);
    }
    
    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityAgents->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('agents/index');
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];
      $countPhones = $this->entityPhones->fetchAll(['agent_id' => $id]);
//         if($countPhones > 0){
//             $checkRelationship['phones'] = 1;
//         }

        if($request->isPost()){
            // delete agents_id in phones
//             $data = ['agents_id' => null];
//             $this->entityPhones->updateRows(['agent_id' => $id], $data);
            // delete agents
            $this->entityAgents->updateRow($id, ['status' => '-1']);
            $this->flashMessenger()->addSuccessMessage('X??a d??? li???u th??nh c??ng!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form, 
            'checkRelationship' => $checkRelationship, 
            'valueCurrent' => $valueCurrent
        ]);
    }
    
    public function iframeExportsAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $id = (int)$this->params()->fromRoute('id', 0);
        //echo $id;die();
        $valueCurrent = $this->entityAgents->fetchRow($id);
        if(empty($valueCurrent)){
            die('success');
        }
        $view->setVariable('id', $id);
        return $view;
    }
}
