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

        // xem đơn hàng khi tài khoản đăng nhập có nhiệm vụ phải làm với đơn hàng đó
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
        $form->get('company_id')->setValueOptions(['' => '--- Chọn một khách hàng ---'] + $optionCompanies);
        // get addresses
        $form->get('addresses_id')->setValueOptions(['' => '--- Địa chỉ khách hàng ---']);
        // get surrogates
        $form->get('surrogates_id')->setValueOptions(['' => '--- Người liên hệ ---']);

        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            if($valuePost['company_id'] != ''){
                $optionAddresses = $this->entityAddresses->fetchAllToOptions($valuePost['company_id']);
                $form->get('addresses_id')->setValueOptions(['' => '--- Chọn một địa chỉ ---'] + $optionAddresses);
                $optionSurrogates = $this->entitySurrogates->fetchAllToOptions($valuePost['company_id']);
                $form->get('surrogates_id')->setValueOptions(['' => '--- Chọn một người đại diện ---'] + $optionSurrogates);
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
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
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
        $form->get('company_id')->setValueOptions(['' => '--- Chọn một khách hàng ---'] + $optionCompanies);
        if($valuePost['company_id'] != ''){
            $optionAddresses = $this->entityAddresses->fetchAllToOptions($valuePost['company_id']);
            $form->get('addresses_id')->setValueOptions(['' => '--- Chọn một địa chỉ ---'] + $optionAddresses);
            $optionSurrogates = $this->entitySurrogates->fetchAllToOptions($valuePost['company_id']);
            $form->get('surrogates_id')->setValueOptions(['' => '--- Chọn một người đại diện ---'] + $optionSurrogates);
        }else{
            $form->get('addresses_id')->setValueOptions(['' => '--- Địa chỉ khách hàng ---']);
            $form->get('surrogates_id')->setValueOptions(['' => '--- Người liên hệ ---']);
        }
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                if($valuePost['company_id'] != ''){
                    $optionAddresses = $this->entityAddresses->fetchAllToOptions($valuePost['company_id']);
                    $form->get('addresses_id')->setValueOptions(['' => '--- Chọn một địa chỉ ---'] + $optionAddresses);
                    $optionSurrogates = $this->entitySurrogates->fetchAllToOptions($valuePost['company_id']);
                    $form->get('surrogates_id')->setValueOptions(['' => '--- Chọn một người đại diện ---'] + $optionSurrogates);
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
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
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
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
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
		    $this->flashMessenger()->addWarningMessage('Không đủ quyền truy cập, đề nghị liên hệ Admin để biết thêm chi tiết!');
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
		    $this->flashMessenger()->addWarningMessage('Không đủ quyền truy cập, đề nghị liên hệ Admin để biết thêm chi tiết!');
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
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xuất dữ liệu, đề nghị liên hệ Admin!');
            return $this->redirect()->toRoute('storehouses/agents');
        }
        // Đặt tên file
        $path = "Ke-hoach-san-xuat-" . $this->defineCompanyId . "-" . date('Y-m-d-H-i-s') . ".xlsx";
        	
        /* Tạo mới một đối tượng PHPExcel */
        $objPHPExcel = new \PHPExcel();
        /* Cài đặt Properties */
        $objPHPExcel->getProperties()->setCreator("Pxt Creator")
        ->setLastModifiedBy("Hisu Modified")
        ->setTitle("Hisu Title")
        ->setSubject("Hisu Subject")
        ->setDescription("Hisu Description")
        ->setKeywords("Hisu Keywords")
        ->setCategory("Hisu Category");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')
        ->setSize(11); /* Cài đặt font cho cả file */
        	
        /* Cài đặt chiều rộng cho từng ô */
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
        ->setCellValue('B1', 'NHÓM SP')
        ->setCellValue('C1', 'TÊN KH')
        ->setCellValue('D1', 'SỐ LƯỢNG KH')
        ->setCellValue('E1', 'SL ĐÃ GIAO')
        ->setCellValue('F1', 'SL CÒN LẠI')
        ->setCellValue('G1', 'NGÀY GIAO')
        ->setCellValue('H1', 'NGÀY KH NHẬN HÀNG')
        ->setCellValue('I1', 'XUẤT KẼM')
        ->setCellValue('J1', 'OFFSET')
        ->setCellValue('K1', 'KTS')
        ->setCellValue('L1', 'BẾ')
        ->setCellValue('M1', 'KÉO LỤA')
        ->setCellValue('N1', 'KCS KIỂM TRA');
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
        // Truyền dữ liệu vào file
        $i = 2; // số bắt đầu
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
            ->setCellValue("I" . $i, (isset($mission3) && $mission3['status'] == 3)? "Xong" : "Chưa xong")
            ->setCellValue("J" . $i, !empty($mission4) ? (($mission4['status'] == 3) ? "Xong" : "Chưa xong") :"X")
            ->setCellValue("K" . $i, !empty($mission5) ? (($mission5['status'] == 3) ? "Xong" : "Chưa xong") :"X");
            $i++;
            $j++;
        }
        //Zend_Debug::dump($arrMessage); die("PXT");
        //unset($arrCodes);
        // Redirect output to a client’s web browser (Excel2007)
        ob_end_clean(); /* Xoá (hay loại bỏ) bất kỳ khoảng trắng trong cặp lệnh này*/
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