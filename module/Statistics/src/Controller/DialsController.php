<?php

namespace Statistics\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Promotions\Model\ListDials;
use Promotions\Model\Promotions;
use Statistics\Form\Dials\SearchForm;
use Promotions\Model\Dials;
use Statistics\Form\Dials\SearchWinnerForm;
use Promotions\Model\WinnerDials;
use Promotions\Model\Prizes;

class DialsController extends AdminCore{
    
    public $entitySettings;
    public $entityListDials;
    public $entityPromotions;
    public $entityDials;
    public $entityWinnerDials;
    public $entityPrizes;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, 
        ListDials $entityListDials, Promotions $entityPromotions, Dials $entityDials, WinnerDials $entityWinnerDials,
        Prizes $entityPrizes) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityListDials = $entityListDials;
        $this->entityPromotions = $entityPromotions;
        $this->entityDials = $entityDials;
        $this->entityWinnerDials = $entityWinnerDials;
        $this->entityPrizes = $entityPrizes;
    }
    
    public function indexAction(){
        $formSearch = new SearchForm();
        $formSearch->get('dials_id')->setValueOptions( ['' => '------- Chọn một chương trình quay số -------'] + $this->entityDials->fetchAllConditions(['re_options' => ['key' => '[id]', 'value' => '[name]']]) );
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        if(isset($queries['btnExport'])){
            $this->exportIndex($queries);
        }
        
        $arrListDials = new Paginator(new ArrayAdapter( $this->entityListDials->fetchAllConditions($queries) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrListDials->setCurrentPageNumber($page);
        $arrListDials->setItemCountPerPage($perPage);
        $arrListDials->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrListDials' => $arrListDials, 
            'contentPaginator' => $contentPaginator,
            'optionPromotions' => $this->entityPromotions->fetchAllConditions(['re_options' => ['key' => '[id]', 'value' => '[name]'], 'dial' => 0]),
            'formSearch' => $formSearch,
            'queries' => $queries
        ]);
    }

    public function exportIndex($queries = null){
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        // get data
        $arrListDials = $this->entityListDials->fetchAllConditions($queries);
        if(empty($arrListDials)) return true;
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xuất dữ liệu, đề nghị liên hệ Admin!');
            return $this->redirect()->toRoute('statistics/dials');
        }
        // Đặt tên file
        $path = "Thong-ke-tham-gia-quay-so-" . date('d-m-Y-H-i-s') . ".xlsx";
        	
        /* Tạo mới một đối tượng PHPExcel */
        $objPHPExcel = new \PHPExcel();
        /* Cài đặt Properties */
        $objPHPExcel->getProperties()->setCreator("Pxt Creator")
        ->setLastModifiedBy("Pxt Modified")
        ->setTitle("Pxt Title")
        ->setSubject("Pxt Subject")
        ->setDescription("Pxt Description")
        ->setKeywords("Pxt Keywords")
        ->setCategory("Pxt Category");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')
        ->setSize(12); /* Cài đặt font cho cả file */
        	
        /* Cài đặt chiều rộng cho từng ô */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        	
        /* Chỉnh dòng đầu tiên */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:E1'); /* Nhóm cột */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Thống kê danh sách tham gia quay số");
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_DARKGREEN ) );
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'STT')
        ->setCellValue('B2', 'Mã PIN')
        ->setCellValue('C2', 'Số điện thoại')
        ->setCellValue('D2', 'Chương trình khuyến mãi')
        ->setCellValue('E2', 'Ngày tham gia');
        $objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getFont()->setBold(true);
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        // Truyền dữ liệu vào file
        $i = 3; // số bắt đầu
        $j = 1;
        $optionPromotions = $this->entityPromotions->fetchAllToOptions(['dial' => 0]);
        //\Zend\Debug\Debug::dump($optionPromotions); die();
        foreach($arrListDials as $item){
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A" . $i, $j)
            ->setCellValue("B" . $i, $item['codes_id'])
            ->setCellValue("C" . $i, $item['phones_id'])
            ->setCellValue("D" . $i, $optionPromotions[$item['promotions_id']])
            ->setCellValue("E" . $i, "'" . date_format(date_create($item['created_at']), 'd/m/Y H:i:s'));
            $i++;
            $j++;
        }
        unset($arrMessage);
        // Redirect output to a client’s web browser (Excel2007)
        ob_end_clean(); /* Xoá (hay loại bỏ) bất kỳ khoảng trắng trong cặp lệnh này*/
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
    
    public function winnerAction(){
        $formSearch = new SearchWinnerForm();
        $formSearch->get('dials_id')->setValueOptions( ['' => '------- Chọn một chương trình quay số -------'] + $this->entityDials->fetchAllConditions(['re_options' => ['key' => '[id]', 'value' => '[name]']]) );
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        if(isset($queries['btnExport'])){
            $this->exportWinner($queries);
        }
        $arrWinnerDials = new Paginator(new ArrayAdapter( $this->entityWinnerDials->fetchAllConditions($queries) ));
        // get setting paginator
        $settingPaginator = $this->entitySettings->fetchRow('paginator');
        $contentPaginator = json_decode($settingPaginator['content'], true);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrWinnerDials->setCurrentPageNumber($page);
        $arrWinnerDials->setItemCountPerPage($perPage);
        $arrWinnerDials->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrWinnerDials' => $arrWinnerDials, 
            'contentPaginator' => $contentPaginator,
            'optionPrizes' => $this->entityPrizes->fetchAllConditions(['key_is_id' => '1']),
            'optionDials' => $this->entityDials->fetchAllConditions(['re_options' => ['key' => '[id]', 'value' => '[name]']]),
            'formSearch' => $formSearch,
            'queries' => $queries
        ]);
    }

    public function exportWinner($queries = null){
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        // get data
        $arrWinnerDials = $this->entityWinnerDials->fetchAllConditions($queries);
        if(empty($arrWinnerDials)) return true;
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xuất dữ liệu, đề nghị liên hệ Admin!');
            return $this->redirect()->toRoute('statistics/dials', ['action' => 'winner']);
        }
        // Đặt tên file
        $path = "Thong-ke-trung-thuong-quay-so-" . date('d-m-Y-H-i-s') . ".xlsx";
        	
        /* Tạo mới một đối tượng PHPExcel */
        $objPHPExcel = new \PHPExcel();
        /* Cài đặt Properties */
        $objPHPExcel->getProperties()->setCreator("Pxt Creator")
        ->setLastModifiedBy("Pxt Modified")
        ->setTitle("Pxt Title")
        ->setSubject("Pxt Subject")
        ->setDescription("Pxt Description")
        ->setKeywords("Pxt Keywords")
        ->setCategory("Pxt Category");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')
        ->setSize(12); /* Cài đặt font cho cả file */
        	
        /* Cài đặt chiều rộng cho từng ô */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(100);
        	
        /* Chỉnh dòng đầu tiên */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:G1'); /* Nhóm cột */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Thống kê danh sách trúng thưởng quay số");
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_DARKGREEN ) );
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'STT')
        ->setCellValue('B2', 'Mã trúng thưởng')
        ->setCellValue('C2', 'Số điện thoại')
        ->setCellValue('D2', 'Chương trình quay số')
        ->setCellValue('E2', 'Giải thưởng')
        ->setCellValue('F2', 'Ngày trúng thưởng')
        ->setCellValue('G2', 'Tin nhắn trúng thưởng');
        $objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getFont()->setBold(true);
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        // Truyền dữ liệu vào file
        $i = 3; // số bắt đầu
        $j = 1;
        $optionPrizes = $this->entityPrizes->fetchAllConditions(['key_is_id' => '1']);
        $optionDials = $this->entityDials->fetchAllConditions(['re_options' => ['key' => '[id]', 'value' => '[name]']]);
        //\Zend\Debug\Debug::dump($optionPromotions); die();
        foreach($arrWinnerDials as $item){
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A" . $i, $j)
            ->setCellValue("B" . $i, $item['codes_id'])
            ->setCellValue("C" . $i, $item['phones_id'])
            ->setCellValue("D" . $i, (isset($optionPrizes[$item['prizes_id']]['dials_id']) ? $optionDials[$optionPrizes[$item['prizes_id']]['dials_id']] : ''))
            ->setCellValue("E" . $i, (isset($optionPrizes[$item['prizes_id']]['name']) ? $optionPrizes[$item['prizes_id']]['name'] : ''))
            ->setCellValue("F" . $i, "'" . date_format(date_create($item['created_at']), 'd/m/Y H:i:s'))
            ->setCellValue("G" . $i, $item['message']);
            $i++;
            $j++;
        }
        unset($arrWinnerDials);
        // Redirect output to a client’s web browser (Excel2007)
        ob_end_clean(); /* Xoá (hay loại bỏ) bất kỳ khoảng trắng trong cặp lệnh này*/
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
}
