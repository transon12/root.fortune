<?php

namespace Settings\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Settings\Form\Phones\AddForm;
use Settings\Form\Phones\EditForm;
use Settings\Form\Phones\DeleteForm;
use Settings\Model\Settings;
use Settings\Model\Phones;
use Settings\Form\Phones\SearchForm;
use Storehouses\Model\Agents;
use Settings\Form\Phones\ImportForm;
use Zend\Filter\File\RenameUpload;

class PhonesController extends AdminCore{
    public $entityPhones;
    public $entitySettings;
    public $entityAgents;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, Phones $entityPhones, Agents $entityAgents) {
        parent::__construct($entityPxtAuthentication);
        $this->entityPhones = $entityPhones;
        $this->entitySettings = $entitySettings;
        $this->entityAgents = $entityAgents;
    }
    
    public function indexAction(){
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        
        $formImport = new ImportForm();
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
           // \Zend\Debug\Debug::dump($valuePost); die();
            if(isset($valuePost['btnSample'])){
                $this->exportSample();
            }elseif(isset($valuePost['btnImport'])){
                $formImport->setData($valuePost);
                if($formImport->isValid()){
                    $file = $request->getFiles()->toArray();
                    //upload background
                    if($file['file_import']['name'] != ''){
                        $url = $this->uploadFile($file, 'file_import');
                        if($this->importPhones($url, '')){
                            $this->flashMessenger()->addSuccessMessage('Th??m d??? li???u th??nh c??ng!');
                        }else{
                            $this->flashMessenger()->addWarningMessage('File c?? v???n ?????, ????? ngh??? ki???m tra l???i!');
                        }
                    }
                }
            }
        }
        
        $arrPhones = new Paginator(new ArrayAdapter( $this->entityPhones->fetchAll($queries) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrPhones->setCurrentPageNumber($page);
        $arrPhones->setItemCountPerPage($perPage);
        $arrPhones->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrPhones' => $arrPhones, 
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'formImport' => $formImport,
            'queries' => $queries
        ]);
    }
    
    private function uploadFile($file = null, $param = null){
        if($file == null || $param == null) return '';
        $info = pathinfo($file[$param]['name']);
        $url = 'imports-data/' . $info['filename'] . '_' . time() . '.' . $info['extension'];
        $fileUpload = new RenameUpload([
            'target' => FILES_UPLOAD . $url,
            'randomize' => false
        ]);
        $fileUpload->filter($file[$param]);
        return $url;
    }
    
    public function addAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new AddForm($request->getRequestUri());
        $arrAgents = $this->entityAgents->fetchAllToOptions();
        $form->get('agents_id')->setValueOptions( ['' => '------- Ch???n m???t ?????i l?? -------'] + $arrAgents );
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            // check phone was exist
            $isValid = $form->isValid();
            if($isValid && !empty( $this->entityPhones->fetchRow(\Pxt\String\ChangeString::convertPhoneVn($valuePost['id'])) )){
                $form->get('id')->setMessages($form->get('id')->getMessages() + ['phone_exist' => 'S??? ??i???n tho???i n??y ???? t???n t???i!']);
                $isValid = false;
            }
            if($isValid){
                $data = [
                    'agents_id' => (isset($valuePost['agents_id']) ? $valuePost['agents_id'] : null),
                    'id' => \Pxt\String\ChangeString::convertPhoneVn($valuePost['id']),
                    'fullname' => $valuePost['fullname'],
                    'address' => $valuePost['address'],
                    'lock_time' => $valuePost['lock_time'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                    'locked_at' => ($valuePost['lock_time'] > 0) ? \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent() : null
                ];
                $this->entityPhones->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Th??m d??? li???u th??nh c??ng!');
                die('success');
            }else{
            //\Zend\Debug\Debug::dump($form->get('id')); die();
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }
        
        return new ViewModel(['form' => $form]);
    }
    
    public function editAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = $this->params()->fromRoute('id', '');
        $valueCurrent = $this->entityPhones->fetchRow($id);
        
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('settings/phones');
        }else{
            $valuePost = $valueCurrent; 
        }
        
        $form = new EditForm($request->getRequestUri());
        $form->get('id')->setAttribute('readonly', true);
        
        $arrAgents = $this->entityAgents->fetchAllToOptions();
        $form->get('agents_id')->setValueOptions( ['' => '------- Ch???n m???t ?????i l?? -------'] + $arrAgents );
        
        $form->setData($valuePost);
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'agents_id' => (isset($valuePost['agents_id']) ? $valuePost['agents_id'] : null),
                    'fullname' => $valuePost['fullname'],
                    'address' => $valuePost['address'],
                    'lock_time' => $valuePost['lock_time'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                    'locked_at' => ($valuePost['lock_time'] > 0) ? \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent() : null
                ];
                $this->entityPhones->updateRow($id, $data);
                $this->flashMessenger()->addSuccessMessage('S???a d??? li???u th??nh c??ng!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }
        
        return new ViewModel(['form' => $form]);
    }
    
    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = $this->params()->fromRoute('id', '');
        $valueCurrent = $this->entityPhones->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('settings/phones');
        }
        $form = new DeleteForm($request->getRequestUri());
        if($request->isPost()){
            $this->entityPhones->deleteRow($id);
            $this->flashMessenger()->addSuccessMessage('X??a d??? li???u th??nh c??ng!');
            die('success');
        }
        
        return new ViewModel(['form' => $form]);
    }

    public function exportSample(){
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('C?? l???i trong qu?? tr??nh xu???t d??? li???u, ????? ngh??? li??n h??? Admin!');
            return $this->redirect()->toRoute('settings/phones');
        }
        // ?????t t??n file
        $path = "Mau-nhap-so-dien-thoai-dai-ly.xlsx";
        	
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        	
        /* Ch???nh d??ng ?????u ti??n */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:D1'); /* Nh??m c???t */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("M???u nh???p s??? ??i???n tho???i cho ?????i l??");
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_DARKGREEN ) );
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'T??n ?????i l??')
        ->setCellValue('B2', 'M?? ?????i l??')
        ->setCellValue('C2', 'S??? ??i???n tho???i')
        ->setCellValue('D2', 'T??n ng?????i s??? h???u s??? ??T');
        $objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getFont()->setBold(true);
        
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
	
	/**
	 * import data
	 */
	function importPhones($url = "", $fileName){
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
            return $this->redirect()->toRoute('settings/phones');
        }
		
		// Load n???i dung file l??n ????? x??? l??
		try {
			$objPHPExcel = \PHPExcel_IOFactory::load(FILES_UPLOAD . $url);
		} catch(Exception $e) {
			die("L???i load th?? vi???n '".pathinfo($url, PATHINFO_BASENAME) . "': " . $e->getMessage());
		}
		
		// L???y d??? li???u t???i sheet m???c ?????nh
		$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		
		$n = 1;
		if(!empty($sheetData)){
			foreach($sheetData as $itemSheet){
				if($n >= 3){
				    if(strlen($itemSheet['B']) > 0 && strlen($itemSheet['C']) > 0){
				        // check code agent
    				    $currentAgent = $this->entityAgents->fetchRowCode($itemSheet['B']);
    				    $agentId = 0;
    				    if(empty($currentAgent)){
    				        $dataAgent = [];
    				        $dataAgent = [
    				            'name' => $itemSheet['A'],
    				            'code' => $itemSheet['B'],
                                'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
    				        ];
    				        $agentId = $this->entityAgents->addRow($dataAgent);
    				    }else{
    				        $agentId = $currentAgent['id'];
    				    }
    				    // check phone
    				    $phone = \Pxt\String\ChangeString::convertPhoneVn($itemSheet['C']);
    				    $currentPhone = $this->entityPhones->fetchRow($phone);
    				    if(empty($currentPhone)){
    				        $dataPhone = [];
    				        $dataPhone = [
    				            'id' => $phone,
    				            'agents_id' => $agentId,
    				            'fullname' => $itemSheet['D'],
    				            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
    				        ];
    				        $this->entityPhones->addRow($dataPhone);
    				    }else{
    				        $dataPhone = [];
    				        $dataPhone = [
    				            'agents_id' => $agentId,
    				            'fullname' => ($itemSheet['D'] != '') ? $itemSheet['D'] : $currentPhone['fullname']
    				        ];
    				        $this->entityPhones->updateRow($phone, $dataPhone);
    				    }
				    }
				}
				$n++;
			}
		}
		return true;
	}
}
