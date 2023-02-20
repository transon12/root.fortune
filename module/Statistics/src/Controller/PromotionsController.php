<?php

namespace Statistics\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Products\Model\Products;
use Settings\Model\Settings;
use Promotions\Model\ListPromotions;
use Statistics\Form\Promotions\SearchForm;
use Promotions\Model\Promotions;
use Statistics\Form\Promotions\SearchWinnerForm;
use Promotions\Model\WinnerPromotions;

class PromotionsController extends AdminCore{
    
    public $entitySettings;
    public $entityListPromotions;
    public $entityProducts;
    public $entityPromotions;
    public $entityWinnerPromotions;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, 
        ListPromotions $entityListPromotions, Products $entityProducts, Promotions $entityPromotions,
        WinnerPromotions $entityWinnerPromotions) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityListPromotions = $entityListPromotions;
        $this->entityProducts = $entityProducts;
        $this->entityPromotions = $entityPromotions;
        $this->entityWinnerPromotions = $entityWinnerPromotions;
    }
    
    public function indexAction(){
        $formSearch = new SearchForm();
        $optionPromotions = $this->entityPromotions->fetchAllOptions01(['company_id' => COMPANY_ID, 'dial' => 0]);
       // \Zend\Debug\Debug::dump($optionPromotions); die();
        $formSearch->get('promotions_id')->setValueOptions( ['' => '------- Chọn một chương trình khuyến mãi -------'] + $optionPromotions );
        
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        if(isset($queries['btnExport'])){
            $this->exportIndex($queries);
        }
        
        $arrListPromotions = new Paginator(new ArrayAdapter( $this->entityListPromotions->fetchAlls($queries + ['company_id' => COMPANY_ID]) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrListPromotions->setCurrentPageNumber($page);
        $arrListPromotions->setItemCountPerPage($perPage);
        $arrListPromotions->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrListPromotions' => $arrListPromotions, 
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries
        ]);
    }

    public function exportIndex($queries = null){
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        // get data
        $arrListPromotions = $this->entityListPromotions->fetchAlls($queries + ['company_id' => COMPANY_ID]);
        if(empty($arrListPromotions)) return true;
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xuất dữ liệu, đề nghị liên hệ Admin!');
            return $this->redirect()->toRoute('statistics/promotions');
        }
        // Đặt tên file
        $path = "Thong-ke-tin-nhan-" . date('d-m-Y-H-i-s') . ".xlsx";
        	
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        	
        /* Chỉnh dòng đầu tiên */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1'); /* Nhóm cột */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Thống kê danh sách tham gia khuyến mãi");
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_DARKGREEN ) );
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'STT')
        ->setCellValue('B2', 'Mã PIN')
        ->setCellValue('C2', 'Số điện thoại')
        ->setCellValue('D2', 'Loại chương trình')
        ->setCellValue('E2', 'Điểm')
        ->setCellValue('F2', 'Ngày tham gia');
        $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getFont()->setBold(true);
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
        $arrIsType = Promotions::returnIsType();
        foreach($arrListPromotions as $item){
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A" . $i, $j)
            ->setCellValue("B" . $i, $item['code_id'])
            ->setCellValue("C" . $i, $item['phone_id'])
            ->setCellValue("D" . $i, $arrIsType[$item['is_type']])
            ->setCellValue("E" . $i, $item['score'])
            ->setCellValue("F" . $i, "'" . date_format(date_create($item['created_at']), 'd/m/Y H:i:s'));
            $i++;
            $j++;
        }
        //Zend_Debug::dump($arrMessage); die("PXT");
        unset($arrListPromotions);
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
        $optionPromotions = $this->entityPromotions->fetchAllConditions(['re_options' => ['key' => '[id]', 'value' => '[name]'], 'dial' => 0]);
        $formSearch->get('promotions_id')->setValueOptions( ['' => '------- Chọn một chương trình khuyến mãi -------'] + $optionPromotions );
        
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        if(isset($queries['btnExport'])){
            $this->exportWinner($queries);
        }
        
        $arrWinnerPromotions = new Paginator(new ArrayAdapter( $this->entityWinnerPromotions->fetchAll($queries) ));
        // get setting paginator
        $settingPaginator = $this->entitySettings->fetchRow('paginator');
        $contentPaginator = json_decode($settingPaginator['content'], true);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrWinnerPromotions->setCurrentPageNumber($page);
        $arrWinnerPromotions->setItemCountPerPage($perPage);
        $arrWinnerPromotions->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrWinnerPromotions' => $arrWinnerPromotions, 
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'optionPromotions' => $optionPromotions,
        ]);
    }

    public function exportWinner($queries = null){
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        // get data
        $arrWinnerPromotions = $this->entityWinnerPromotions->fetchAll($queries);
        if(empty($arrWinnerPromotions)) return true;
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xuất dữ liệu, đề nghị liên hệ Admin!');
            return $this->redirect()->toRoute('statistics/promotions', ['action' => 'winner']);
        }
        // Đặt tên file
        $path = "Thong-ke-trung-thuong-khuyen-mai-" . date('d-m-Y-H-i-s') . ".xlsx";
        	
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(100);
        	
        /* Chỉnh dòng đầu tiên */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1'); /* Nhóm cột */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Thống kê danh sách trúng thưởng khuyến mãi");
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_DARKGREEN ) );
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'STT')
        ->setCellValue('B2', 'Số điện thoại')
        ->setCellValue('C2', 'Chương trình khuyến mãi')
        ->setCellValue('D2', 'Điểm')
        ->setCellValue('E2', 'Ngày trúng thưởng')
        ->setCellValue('F2', 'Tin nhắn trả ra');
        $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getFont()->setBold(true);
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        // Truyền dữ liệu vào file
        $i = 3; // số bắt đầu
        $j = 1;
        $optionPromotions = $this->entityPromotions->fetchAllToOptions(['dial' => 0]);
        foreach($arrWinnerPromotions as $item){
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A" . $i, $j)
            ->setCellValue("B" . $i, $item['phones_id'])
            ->setCellValue("C" . $i, (isset($optionPromotions[$item['promotions_id']]) ? $optionPromotions[$item['promotions_id']] : ""))
            ->setCellValue("D" . $i, $item['score'])
            ->setCellValue("E" . $i, date_format(date_create($item['created_at']), 'd/m/Y H:i:s'))
            ->setCellValue("F" . $i, $item['message']);
            $i++;
            $j++;
        }
        //Zend_Debug::dump($arrMessage); die("PXT");
        unset($arrWinnerPromotions);
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
