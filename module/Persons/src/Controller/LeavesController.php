<?php
namespace Persons\Controller;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Persons\Form\Leaves\DeleteForm;
use Persons\Form\Leaves\EditForm;
use Persons\Form\Leaves\ResetForm;
use Persons\Form\Leaves\ResetOldLeaveForm;
use Persons\Form\Leaves\SearchForm;
use Persons\Form\Leaves\ViewSearchForm;
use Persons\Model\LeaveLists;
use Persons\Model\LeaveRequests;
use Persons\Model\Profiles;
use Settings\Model\Settings;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class LeavesController extends AdminCore{

    public $entitySettings;
    public $entityUsers;
    public $entityProfiles;
    public $entityLeaveLists;
    public $entityLeaveRequests;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Users $entityUsers, Profiles $entityProfiles, LeaveLists $entityLeaveLists, LeaveRequests $entityLeaveRequests){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityUsers = $entityUsers;
        $this->entityProfiles = $entityProfiles;
        $this->entityLeaveLists = $entityLeaveLists;
        $this->entityLeaveRequests = $entityLeaveRequests;
    }

    public function indexAction(){
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        if(isset($queries['btnExport'])){
            $this->exportExcelAnualLeaveAction($queries);
        }
        $arrLeaveLists = new Paginator(new ArrayAdapter($this->entityLeaveLists->fetchAlls($queries)));
        // \Zend\Debug\Debug::dump($arrLeaveLists); die();
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrLeaveLists->setCurrentPageNumber($page);
        $arrLeaveLists->setItemCountPerPage($perPage);
        $arrLeaveLists->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'formSearch' => $formSearch,
            'arrLeaveLists' => $arrLeaveLists,
            'contentPaginator' => $contentPaginator,
            'optionProfileName' => $this->entityProfiles->fetchAllByName(), 
            'optionProfileStartDate' => $this->entityProfiles->fetchStartDateById(),
        ]);
    }

    public function editAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = $this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityLeaveLists->fetchRowAsId($id);
        // \Zend\Debug\Debug::dump($valueCurrent); die();
        if(empty($valueCurrent)){
            die('Not found!');
        }
        $valuePost = $valueCurrent;
        $request = $this->getRequest();
        $form = new EditForm($request->getRequestUri());
        $form->setData($valuePost);
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'total_leave' => $valuePost['total_leave'],
                    'leave_day_used' => $valuePost['leave_day_used'],
                ];
                $this->entityLeaveLists->updateRow($valueCurrent['user_id'], $data);
                $this->flashMessenger()->addSuccessMessage('S???a d??? li???u th??nh c??ng!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }
        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = $this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityLeaveLists->fetchRowAsId($id);
        if(empty($valueCurrent)){
            die('Not found!');
        }
        $request = $this->getRequest();
        $form = new DeleteForm($request->getRequestUri());
        if($request->isPost()){
            $this->entityLeaveLists->updateRow($valueCurrent['user_id'], ['status'=> '-1']);
            $this->flashMessenger()->addSuccessMessage('X??a d??? li???u th??nh c??ng!');
            die('success');
        }
        return new ViewModel([
            'form' => $form
        ]);
    }

    public function viewAction(){
        $id = $this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityLeaveLists->fetchRowAsId($id);
        if(empty($valueCurrent)){
            die('Not found!');
        }
        $formSearch = new ViewSearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        $arrLeaveRequests=[];
        if(isset($queries['btnSearch'])){
            $arrLeaveRequests = $this->entityLeaveRequests->fetchAllByUserId($valueCurrent['user_id'], $queries);
        }
        return new ViewModel([
            'arrLeaveRequests' => $arrLeaveRequests,
            // 'contentPaginator' => $contentPaginator,
            'optionProfileName' => $this->entityProfiles->fetchAllByName(), 
            'optionProfileStartDate' => $this->entityProfiles->fetchStartDateById(),
            'currentName' => $this->entityProfiles->fetchNameByUserId(),
            'formSearch' => $formSearch,
        ]);
    }

    public function resetOldLeaveAction(){
        $this->layout()->setTemplate('empty/layout');
        $allValue = $this->entityLeaveLists->fetchAlls();
        $request = $this->getRequest();
        $form = new ResetOldLeaveForm($request->getRequestUri());
        if($request->isPost()){
            foreach($allValue as $item){
                $leaveDayUsed = $item['leave_day_used'] - $item['old_year_leave'];
                $data = [ 
                    'leave_day_used' => ($leaveDayUsed > 0) ? $leaveDayUsed : 0,
                    'old_year_leave' => 0,
                ];
                $this->entityLeaveLists->updateRow($item['user_id'], $data);
            }
            $this->flashMessenger()->addSuccessMessage('Reset d??? li???u th??nh c??ng!');
            die('success');
        }
        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function iframeAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $allValue = $this->entityLeaveLists->fetchAlls();
        return $view;
    }

    public function resetAction(){
        $this->layout()->setTemplate('iframe/layout');
        $allValue = $this->entityLeaveLists->fetchAlls();
        $optionProfileStartDate = $this->entityProfiles->fetchStartDateById();
        // \Zend\Debug\Debug::dump($allValue); 
        $request = $this->getRequest();
        $form = new ResetForm();
        if($request->isPost()){
            $this->exportExcelAction();

            foreach($allValue as $item){
                $dateStart = (date_create($optionProfileStartDate[$item['profile_id']]));
                $dateCurrent = date_create(\Pxt\Datetime\ChangeDatetime::getDateCurrent());
                $dateDifference = date_diff($dateStart, $dateCurrent)->format('%y');
                $totalLeavePlus = floor($dateDifference/5);

                $leaveDayUsed = $item['total_leave'] - $item['leave_day_used'];
                if($leaveDayUsed == 0){
                    $leaveDayUsed = 1;
                }elseif($leaveDayUsed == 0.5){
                    $leaveDayUsed = 0.5;
                }else{
                    $leaveDayUsed = 0;
                }

                $data = [ 
                    'total_leave' => 12 + $totalLeavePlus,
                    'leave_day_used' => 0,
                    'old_year_leave' => $item['total_leave'] - $item['leave_day_used'],
                ];
                $this->entityLeaveLists->updateRow($item['user_id'], $data);
            }
            $this->flashMessenger()->addSuccessMessage('Reset d??? li???u th??nh c??ng!');
            die('success');
        }
        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function exportExcelAction(){
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');

        $allValue = $this->entityLeaveLists->fetchAlls();
        
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('C?? l???i trong qu?? tr??nh xu???t d??? li???u, ????? ngh??? li??n h??? Admin!');
            return $this->redirect()->toRoute('persons/leaves');
        }
        // ?????t t??n file
        $path = "B???ng t???ng h???p ph??p -" . $this->defineCompanyId . "-" . date('Y-m-d-H-i-s') . ".xlsx";
        
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
        ->setSize(12); /* C??i ?????t font cho c??? file */
        
        /* C??i ?????t chi???u r???ng cho t???ng ?? */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(4.5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(13);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(60);
        // Autofit Row height
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
        
        /* Ch???nh d??ng ?????u ti??n */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:H1'); /* Nh??m c???t */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("C??NG TY CP PH??T TRI???N KHOA H???C C??NG NGH??? VINA");
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setSize(11)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_BLACK ) );

        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A2:H2'); /* Nh??m c???t */
        $objPHPExcel->getActiveSheet()->getCell('A2')->setValue("T???NG H???P S??? NG??Y PH??P  N??M 2021");
        $objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_BLACK ) );

        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A3', 'STT')
        ->setCellValue('B3', 'H??? v?? t??n')
        ->setCellValue('C3', 'Ng??y v??o l??m vi???c')
        ->setCellValue('D3', 'Th??m ni??n')
        ->setCellValue('E3', 'S??? ng??y ph??p ???????c h?????ng 2021')
        ->setCellValue('F3', 'Ph??p n??m ???? d??? d???ng')
        ->setCellValue('G3', 'Ph??p c??n l???i 2021')
        ->setCellValue('H3', 'Ph??p ???? s??? d???ng');
        // echo "abc"; die();
            
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3:H3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3:H3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
        $objPHPExcel->getActiveSheet()->getStyle('A3:H3')->getFont()->setBold(true);
        // set wrap text
        //$objPHPExcel->getActiveSheet()->getStyle('A10')->getAlignment()->setWrapText(true);

        $optionProfileName = $this->entityProfiles->fetchAllByName();
        $optionProfileStartDate = $this->entityProfiles->fetchStartDateById();
        // Truy???n d??? li???u v??o file
        $i = 4; // s??? b???t ?????u
        $j = 1;
        foreach($allValue as $item){
            $dateStart = (date_create($optionProfileStartDate[$item['profile_id']]));
            $dateCurrent = date_create(\Pxt\Datetime\ChangeDatetime::getDateCurrent());
            $dateDifference = date_diff($dateStart, $dateCurrent)->format('%y');
            $totalLeavePlus = floor($dateDifference/5);

            $userAnnualLeave = $this->entityLeaveRequests->fetchAllAnnualLeaveByUserId($item['user_id']);
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A" . $i, $j)
            ->setCellValue("B" . $i, $optionProfileName[$item['profile_id']])
            ->setCellValue("C" . $i, date_format($dateStart, "d/m/Y"))
            ->setCellValue("D" . $i, "=DATEDIF(C$i,TODAY(),\"y\")&\"n??m\" &DATEDIF(C$i,TODAY(),\"ym\")&\"th??ng\" &DATEDIF(C$i,TODAY(),\"md\")&\"ng??y\"")
            ->setCellValue("E" . $i, 12 + $totalLeavePlus)
            ->setCellValue("F" . $i, $item['leave_day_used'])
            ->setCellValue("G" . $i, $item['total_leave'] - $item['leave_day_used'])
            ->setCellValue("H" . $i, implode(", ", $userAnnualLeave));
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
        $objWriter->save('php://output');
        // ob_end_clean();
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        // exit;
        return true;
    }

    public function exportExcelAnualLeaveAction($options){
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('C?? l???i trong qu?? tr??nh xu???t d??? li???u, ????? ngh??? li??n h??? Admin!');
            return $this->redirect()->toRoute('persons/leaves');
        }
        // ?????t t??n file
        $path = "T???ng h???p ph??p -" . $this->defineCompanyId . "-" . date('Y-m-d-H-i-s') . ".xlsx";
        
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
        ->setSize(12); /* C??i ?????t font cho c??? file */
        
         /* C??i ?????t chi???u r???ng cho t???ng ?? */
         $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
         $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
         $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
         $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
         $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
         // Autofit Row height
         $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
         
         /* Ch???nh d??ng ?????u ti??n */
         $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
         $objPHPExcel->getActiveSheet()->mergeCells('A1:E1'); /* Nh??m c???t */
         $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("T???NG H???P PH??P TH??NG");
         $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
         $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_BLACK ) );
        
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'STT')
        ->setCellValue('B2', 'H??? v?? t??n')
        ->setCellValue('C2', 'Ngh??? t??? ng??y')
        ->setCellValue('D2', 'Ngh??? t???i ng??y')
        ->setCellValue('E2', 'T???ng s??? ng??y ngh???');
        // echo "abc"; die();
            
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
        $objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getFont()->setBold(true);

        $arrLeaveRequests = $this->entityLeaveRequests->fetchAllOptions($options);
        $optionProfileName = $this->entityProfiles->fetchNameByUserId();
        $optionLeaveRequest = \Persons\Model\LeaveRequests::getOption();
// \Zend\Debug\Debug::dump($arrLeaveRequests);die();
        $i = 3; // s??? b???t ?????u
        $j = 1;
        foreach($arrLeaveRequests as $item){
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A" . $i, $j)
            ->setCellValue("B" . $i, $optionProfileName[$item['user_id']])
            ->setCellValue("C" . $i, date_format(date_create($item['leave_start_date']), "d/m/Y") . " ". $optionLeaveRequest[$item['option_leave_start_date']] ." ")
            ->setCellValue("D" . $i, date_format(date_create($item['leave_stop_date']), "d/m/Y"). " ". $optionLeaveRequest[$item['option_leave_stop_date']] ." ")
            ->setCellValue("E" . $i, $item['total_apply_leave']);
            $i++;
            $j++;
        }
        
        ob_end_clean(); /* Xo?? (hay lo???i b???) b???t k??? kho???ng tr???ng trong c???p l???nh n??y*/
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$path.'"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        ob_end_clean();
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        // exit;
        return true;
    }

}