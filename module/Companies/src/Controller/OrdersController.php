<?php

namespace Companies\Controller;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Companies\Form\Orders\AddForm;
use Companies\Form\Orders\DeleteForm;
use Companies\Form\Orders\EditForm;
use Companies\Form\Orders\SearchForm;
use Companies\Model\Addresses;
use Companies\Model\MissionDetails;
use Companies\Model\Missions;
use Companies\Model\OrderDetails;
use Companies\Model\Orders;
use Companies\Model\Surrogates;
use Settings\Model\Companies;
use Settings\Model\Settings;
use Settings\Model\Technologies;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class OrdersController extends AdminCore{

    public $entitySettings;
    public $entityOrders;
    public $entityCompanies;
    public $entityAddresses;
    public $entitySurrogates;
    public $entityMissionDetails;
    public $entityOrderDetails;
    public $entityUsers;
    public $entityMissions;
    public $entityTechnologies;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Orders $entityOrders, Companies $entityCompanies, Addresses $entityAddresses, Surrogates $entitySurrogates,
    MissionDetails $entityMissionDetails, OrderDetails $entityOrderDetails, Users $entityUsers,
    Missions $entityMissions, Technologies $entityTechnologies){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityOrders = $entityOrders;
        $this->entityCompanies = $entityCompanies;
        $this->entityAddresses = $entityAddresses;
        $this->entitySurrogates = $entitySurrogates;
        $this->entityMissionDetails = $entityMissionDetails;
        $this->entityOrderDetails = $entityOrderDetails;
        $this->entityUsers = $entityUsers;
        $this->entityMissions = $entityMissions;
        $this->entityTechnologies = $entityTechnologies;
    }

    public function indexAction(){
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        $optionsCompanies = $this->entityCompanies->fetchRowUserId($this->sessionContainer->id);

        // xem ????n h??ng khi t??i kho???n ????ng nh???p c?? nhi???m v??? ph???i l??m v???i ????n h??ng ????
        // if($this->sessionContainer->id != 1){
        //     $arrUser = $this->entityUsers->fetchRowAsId($this->sessionContainer->id);
        //     $arrOrderId = $this->entityMissionDetails->fetchRowAsUsers($arrUser['username']);
        //     $arrOrders = new Paginator(new ArrayAdapter( $this->entityOrders->fetchAlls($queries, $arrOrderId)));
        // }else{
        //     $arrOrders = new Paginator(new ArrayAdapter( $this->entityOrders->fetchAlls($queries)));
        // }

        $arrOrders = new Paginator(new ArrayAdapter( $this->entityOrders->fetchAlls($queries, $optionsCompanies)));

        /** Button export Excel */
        // if(isset($queries['btnExport'])){
        //     $this->exportExcelAction();
        // }

        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrOrders->setCurrentPageNumber($page);
        $arrOrders->setItemCountPerPage($perPage);
        $arrOrders->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'formSearch' => $formSearch,
            'arrOrders' =>$arrOrders,
            'queries' => $queries,
            'contentPaginator' => $contentPaginator,
            'optionCompanies' => $this->entityCompanies->fetchAllToOptions()
        ]);
    }

    public function addAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        $form = new AddForm($request->getRequestUri());
        $optionCompanies = $this->entityCompanies->fetchAllByUserId();
        $form->get('company_id')->setValueOptions(['' => '--- Ch???n m???t kh??ch h??ng ---'] + $optionCompanies);
        // get addresses
        $form->get('addresses_id')->setValueOptions(['' => '--- ?????a ch??? kh??ch h??ng ---']);
        // get surrogates
        $form->get('surrogates_id')->setValueOptions(['' => '--- Ng?????i li??n h??? ---']);

        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            if($valuePost['company_id'] != ''){
                $optionAddresses = $this->entityAddresses->fetchAllToOptions($valuePost['company_id']);
                $form->get('addresses_id')->setValueOptions(['' => '--- Ch???n m???t ?????a ch??? ---'] + $optionAddresses);
                $optionSurrogates = $this->entitySurrogates->fetchAllToOptions($valuePost['company_id']);
                $form->get('surrogates_id')->setValueOptions(['' => '--- Ch???n m???t ng?????i ?????i di???n ---'] + $optionSurrogates);
            }
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'code' => $valuePost['code'],
                    'company_id' => $valuePost['company_id'],
                    'addresses_id' => ($valuePost['addresses_id'] != '') ? $valuePost['addresses_id'] : null,
                    'surrogates_id' => ($valuePost['surrogates_id'] != '') ? $valuePost['surrogates_id'] : null,
                    'number_order' => $valuePost['number_order'],
                    'status' => '1',
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                if($valuePost['delivery_request'] != ''){
                    //$valuePost['delivery_request'] = (isset($valuePost['delivery_request']) && strlen($valuePost['delivery_request']) > 0) ? $valuePost['delivery_request'] : time();
                    $deliveryRequest = date_create_from_format('d/m/Y H:i:s', $valuePost['delivery_request']);
                    $data['delivery_request'] = date_format($deliveryRequest, 'Y-m-d H:i:s');
                }
                $this->entityOrders->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Th??m d??? li???u th??nh c??ng!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }
        return new ViewModel([
            'form' => $form
        ]);
    }
    public function editAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = (int)$this->params()->fromRoute('id',0);
        $valueCurrent = $this->entityOrders->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('companies/orders');
        }else{
            $valuePost = $valueCurrent;
        }
        $request = $this->getRequest();
        $form = new EditForm($request->getRequestUri());
        
        $optionCompanies = $this->entityCompanies->fetchAllByUserId();
        $form->get('company_id')->setValueOptions(['' => '--- Ch???n m???t kh??ch h??ng ---'] + $optionCompanies);
        if($valuePost['company_id'] != ''){
            $optionAddresses = $this->entityAddresses->fetchAllToOptions($valuePost['company_id']);
            $form->get('addresses_id')->setValueOptions(['' => '--- Ch???n m???t ?????a ch??? ---'] + $optionAddresses);
            $optionSurrogates = $this->entitySurrogates->fetchAllToOptions($valuePost['company_id']);
            $form->get('surrogates_id')->setValueOptions(['' => '--- Ch???n m???t ng?????i ?????i di???n ---'] + $optionSurrogates);
        }else{
            $form->get('addresses_id')->setValueOptions(['' => '--- ?????a ch??? kh??ch h??ng ---']);
            $form->get('surrogates_id')->setValueOptions(['' => '--- Ng?????i li??n h??? ---']);
        }
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                if($valuePost['company_id'] != ''){
                    $optionAddresses = $this->entityAddresses->fetchAllToOptions($valuePost['company_id']);
                    $form->get('addresses_id')->setValueOptions(['' => '--- Ch???n m???t ?????a ch??? ---'] + $optionAddresses);
                    $optionSurrogates = $this->entitySurrogates->fetchAllToOptions($valuePost['company_id']);
                    $form->get('surrogates_id')->setValueOptions(['' => '--- Ch???n m???t ng?????i ?????i di???n ---'] + $optionSurrogates);
                }
                $data = [
                    'code' => $valuePost['code'],
                    'company_id' => $valuePost['company_id'],
                    'addresses_id' => ($valuePost['addresses_id'] != '') ? $valuePost['addresses_id'] : null,
                    'surrogates_id' => ($valuePost['surrogates_id'] != '') ? $valuePost['surrogates_id'] : null,
                    'number_order' => $valuePost['number_order'],
                    'status' => '1',
                ];
                if($valuePost['delivery_request'] != ''){
                    $deliveryRequest = date_create_from_format('d/m/Y H:i:s', $valuePost['delivery_request']);
                    $data['delivery_request'] = date_format($deliveryRequest, 'Y-m-d H:i:s');
                }
                $this->entityOrders->updateRow($id,$data);
                $this->flashMessenger()->addSuccessMessage('S???a d??? li???u th??nh c??ng!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }else{
            if($valuePost['delivery_request'] != ''){
                $valuePost['delivery_request'] = date_format(date_create($valuePost['delivery_request']), 'd/m/Y H:i:s');
            }
        }
        $form->setData($valuePost);
        return new ViewModel([
            'form' => $form,

        ]);

    }
    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = (int)$this->params()->fromRoute('id',0);
        //echo $id; die();
        $valueCurrent = $this->entityOrders->fetchRow($id);
        if(empty($valueCurrent)){
            die('success');
        }
        $request = $this->getRequest();
        $form = new DeleteForm($request->getRequestUri());

        // $checkRelationship = [];
        // $arrOrderDetails = $this->entityOrderDetails->fetchAllAsOrderId($id);
        // $arrMissionDetails = $this->entityMissionDetails->fetchAll($id);
        //\Zend\Debug\Debug::dump($arrMissionDetails); die();
        if($request->isPost()){
            $this->entityMissionDetails->updateRow(['status' => '-1'],['order_id'=> $id]);
            $this->entityOrderDetails->updateRow(['status' => '-1'],['orders_id' => $id]);
            $this->entityOrders->updateRow($id,['status'=> '-1','deleted_ad' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()]);
            $this->flashMessenger()->addSuccessMessage('X??a d??? li???u th??nh c??ng!');
            die('success');
        }
        return new ViewModel([
            'form' => $form,
            'id' => $id,
            'valueCurrent' => $valueCurrent
        ]);
    }

    public function selectAddressesAction(){
	    $view = new ViewModel();
        $view->setTerminal(true);
        $request = $this->getRequest();
		if($request->isPost()){
		    $valuePost = $request->getPost()->toArray();
		    if(isset($valuePost['id'])){
		        if($valuePost['id'] != null && $valuePost['id'] != ''){
    		        //$customerId = $valuePost['id'];
    		        $optionAddresses = $this->entityAddresses->fetchAllToOptions($valuePost['id']);
    		        $view->setVariable('optionAddresses', $optionAddresses);
		        }else{
		            die();
		        }
		    }else{
		        die();
		    }
		}else{ // when user is access direct to this link
		    $this->flashMessenger()->addWarningMessage('Kh??ng ????? quy???n truy c???p, ????? ngh??? li??n h??? Admin ????? bi???t th??m chi ti???t!');
		    $this->redirect()->toRoute('companies/orders');
		}
		return $view;
	}
    
	public function selectSurrogatesAction(){
	    $view = new ViewModel();
        $view->setTerminal(true);
        $request = $this->getRequest();
		if($request->isPost()){
		    $valuePost = $request->getPost()->toArray();
		    if(isset($valuePost['id'])){
		        if($valuePost['id'] != null && $valuePost['id'] != ''){
    		        $optionSurrogates = $this->entitySurrogates->fetchAllToOptions($valuePost['id']);
    		        $view->setVariable('optionSurrogates', $optionSurrogates);
		        }else{
		            die();
		        }
		    }else{
		        die();
		    }
		}else{ // when user is access direct to this link
		    $this->flashMessenger()->addWarningMessage('Kh??ng ????? quy???n truy c???p, ????? ngh??? li??n h??? Admin ????? bi???t th??m chi ti???t!');
		    $this->redirect()->toRoute('companies/orders');
		}
		return $view;
	}

    public function iframeOrderDetailsAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = $this->params()->fromRoute('id',0);
        $valueCurrent = $this->entityOrders->fetchRow($id);
        if(empty($valueCurrent)){
            die('success');
        }
        return new ViewModel([
            'id' => $id
        ]);
    }

    public function iframeMissionDetailsAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = $this->params()->fromRoute('id',0);
        $valueCurrent = $this->entityOrders->fetchRow($id);
        if(empty($valueCurrent)){
            die('success');
        }
        return new ViewModel([
            'id' => $id
        ]);
    }

    public function exportExcelAction(){
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');

        $queries = $this->params()->fromQuery();
        // get data
        $arrOrders =  $this->entityOrders->fetchAlls($queries);
        // \Zend\Debug\Debug::dump($arrOrders); die();
        if(empty($arrOrders)) return true;
        // get mission
        $optionMissions = $this->entityMissions->fetchAll();
        // get technologies
        $optionTechnologies = $this->entityTechnologies->fetchAllToOption();
        // get company name
        $optionCompanies = $this->entityCompanies->fetchAllToOptions();
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
        $path = "Ke-hoach-san-xuat-" . $this->defineCompanyId . "-" . date('Y-m-d-H-i-s') . ".xlsx";
        	
        /* T???o m???i m???t ?????i t?????ng PHPExcel */
        $objPHPExcel = new \PHPExcel();
        /* C??i ?????t Properties */
        $objPHPExcel->getProperties()->setCreator("Pxt Creator")
        ->setLastModifiedBy("Hisu Modified")
        ->setTitle("Hisu Title")
        ->setSubject("Hisu Subject")
        ->setDescription("Hisu Description")
        ->setKeywords("Hisu Keywords")
        ->setCategory("Hisu Category");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')
        ->setSize(11); /* C??i ?????t font cho c??? file */
        	
        /* C??i ?????t chi???u r???ng cho t???ng ?? */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(6.33);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(17.11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(17.22);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(13.56);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(13.56);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(13.56);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10.11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(13.56);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(9.89);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10.11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10.11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(10.11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(10.11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(10.11);
        // Autofit Row height
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', 'STT')
        ->setCellValue('B1', 'NH??M SP')
        ->setCellValue('C1', 'T??N KH')
        ->setCellValue('D1', 'S??? L?????NG KH')
        ->setCellValue('E1', 'SL ???? GIAO')
        ->setCellValue('F1', 'SL C??N L???I')
        ->setCellValue('G1', 'NG??Y GIAO')
        ->setCellValue('H1', 'NG??Y KH NH???N H??NG')
        ->setCellValue('I1', 'XU???T K???M')
        ->setCellValue('J1', 'OFFSET')
        ->setCellValue('K1', 'KTS')
        ->setCellValue('L1', 'B???')
        ->setCellValue('M1', 'K??O L???A')
        ->setCellValue('N1', 'KCS KI???M TRA');
        $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00FFFF');
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('I')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('J')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('K')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('L')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('M')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('N')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('D2:D1000')->getNumberFormat()->setFormatCode("#,###"); 
        // set wrap text
        //$objPHPExcel->getActiveSheet()->getStyle('A10')->getAlignment()->setWrapText(true);
        // Truy???n d??? li???u v??o file
        $i = 2; // s??? b???t ?????u
        $j = 1;
        foreach($arrOrders as $item){
            $arrOrderDetails = $this->entityOrderDetails->fetchAll($item['id']);
            $arrMissionDetails = $this->entityMissionDetails->fetchAll($item['id']);
            $a = 0;
            foreach($arrOrderDetails as $orderDetail){
                $strOrderDetails[$a] = $optionTechnologies[$orderDetail['technologies_id']];
                $a++;
            }

            $mission3 = $this->entityMissionDetails->fetchRowByMissionId($item['id'],3);
            $mission4 = $this->entityMissionDetails->fetchRowByMissionId($item['id'],4);
            $mission5 = $this->entityMissionDetails->fetchRowByMissionId($item['id'],5);
            // \Zend\Debug\Debug::dump($mission3['status']); die();

            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A" . $i, $j)
            ->setCellValue("B" . $i, implode(" + ",$strOrderDetails))
            ->setCellValue("C" . $i, $optionCompanies[$item['company_id']])
            ->setCellValue("D" . $i, $item['number_order'])
            ->setCellValue("G" . $i, date_format(date_create($item['delivery_request']), 'd/m'))
            ->setCellValue("I" . $i, (isset($mission3) && $mission3['status'] == 3)? "Xong" : "Ch??a xong")
            ->setCellValue("J" . $i, !empty($mission4) ? (($mission4['status'] == 3) ? "Xong" : "Ch??a xong") :"X")
            ->setCellValue("K" . $i, !empty($mission5) ? (($mission5['status'] == 3) ? "Xong" : "Ch??a xong") :"X");
            $i++;
            $j++;
        }
        //Zend_Debug::dump($arrMessage); die("PXT");
        //unset($arrCodes);
        // Redirect output to a client???s web browser (Excel2007)
        ob_end_clean(); /* Xo?? (hay lo???i b???) b???t k??? kho???ng tr???ng trong c???p l???nh n??y*/
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$path.'"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        ob_end_clean();
        $objWriter->save('php://output');
        
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        exit;
        // return true;
    }
}