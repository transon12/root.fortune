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
use Storehouses\Form\Exports\ExportsForm;
use Settings\Model\Settings;
use Storehouses\Model\Products;
use Codes\Model\Codes;
use Storehouses\Form\Exports\SearchForm;
use Storehouses\Model\Agents;
use Storehouses\Model\Storehouses;

class ExportsController extends AdminCore{
    
    public $entityAgents;
    public $entitySettings;
    public $entityProducts;
    public $entityCodes;
    public $entityStorehouses;
    
    public function __construct(Agents $entityAgents, PxtAuthentication $entityPxtAuthentication, 
        Settings $entitySettings, Products $entityProducts, Codes $entityCodes, Storehouses $entityStorehouses) {
        parent::__construct($entityPxtAuthentication);
        $this->entityAgents = $entityAgents;
        $this->entitySettings = $entitySettings;
        $this->entityProducts = $entityProducts;
        $this->entityCodes = $entityCodes;
        $this->entityStorehouses = $entityStorehouses;
    }
    
    public function exportsAction(){
        // check import
        $id = (int)$this->params()->fromRoute('id', 0);
         //echo $id ; die();
        $valueCurrent = $this->entityAgents->fetchRow($id);
        if(empty($valueCurrent)){
            die('Not found!');
        }
        $arrCodes = [];
        // search form
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        if(isset($queries['btnSearch'])){
            $arrCodes = $this->entityCodes->fetchCountByDate($this->defineCompanyId, ['type' => 'exported_at', 'agent_id' => $id] + $queries);
        }
        if(isset($queries['btnExport'])){
            $this->exportExcelAction();
        }
        // call iframe
        $this->layout()->setTemplate('iframe/layout');
        
        $form = new ExportsForm();

        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                // check status
                $status = isset($valuePost['status']) ? $valuePost['status'] : "1";
                // check serial or qrcode
                /**
                 * $isSerial = 1; ~ is qrcode
                 * $isSerial = 2; ~ only one serial
                 * $isSerial = 3; ~ serial begin and serial end
                 */
                $options = [
                    'is_serial' => '1',
                    'value_begin' => null,
                    'value_end' => null
                ];
                $arrQrcodes = explode('=', $valuePost['codes']);
                if(isset($arrQrcodes[1])){
                    $options['value_begin'] = $arrQrcodes[1];
                }else{
                    $arrSerials = explode('-', $valuePost['codes']);
                    if(!isset($arrSerials[1])){
                        $options['is_serial'] = 2; 
                        $options['value_begin'] = trim($arrSerials[0]);
                    }else{
                        $options['is_serial'] = 3;
                        $options['value_begin'] = trim($arrSerials[0]);
                        $options['value_end'] = trim($arrSerials[1]);
                    }
                }
                $arrCodes = $this->entityCodes->fetchAlls($this->defineCompanyId, ['exports' => '1', 'is_all' => '1', 'status' => $status] + $options);
                //\Zend\Debug\Debug::dump($arrCodes); die();
                if(empty($arrCodes)){
                    $valuePost['exported_at'] = (isset($valuePost['exported_at']) && $valuePost['exported_at'] != "") ? $valuePost['exported_at'] : (date("d/m/Y H:i:s", time()));
                    $exportedAt = date_create_from_format('d/m/Y H:i:s', $valuePost['exported_at']);
                    $data = [
                        'agent_id' => ($status == 1) ? $id : null,
                        'exported_at' => ($status == 1) ? date_format($exportedAt, 'Y-m-d H:i:s') : null
                    ];
                    $result = $this->entityCodes->updatesRowAsCondition($this->defineCompanyId, ['data' => $data] + $options);
                    if($result > 0){
                        $this->flashMessenger()->addSuccessMessage(($status == 1) ? 'Thêm dữ liệu thành công!' : 'Xóa dữ liệu thành công!');
                    }else{
                        if($options['is_serial'] == 3){
                            $this->flashMessenger()->addWarningMessage('Không tìm thấy chuỗi này!');
                        }else{
                            $this->flashMessenger()->addWarningMessage('Không tìm thấy tem này!');
                        }
                    }
                }else{
                    if($options['is_serial'] == 3){
                        $this->flashMessenger()->addWarningMessage(($status == 1) ? 'Trong chuỗi này đã có tem được xuất trước đó!' : 'Trong chuỗi này có tem chưa được xuất hoặc đã được xóa trước đó!');
                    }else{
                        $this->flashMessenger()->addWarningMessage(($status == 1) ? 'Tem này này đã được xuất trước đó!' : 'Tem này chưa được xuất!');
                    }
                }
                $form->get('codes')->setValue('');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        
        return new ViewModel([
            'arrCodes'          => $arrCodes, 
            'form'              => $form,
            'optionProducts'    => $this->entityProducts->fetchAllOptions01(['company_id' => COMPANY_ID]),
            'id'                => $id,
            'formSearch'        => $formSearch,
            //'contentPaginator' => $contentPaginator, 
            //'dialId' => $dialId
        ]);
    }

    public function searchData($id){

    }

    public function exportExcelAction(){
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        // check import
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityAgents->fetchRow($id);
        if(empty($valueCurrent)){
            die('Not found!');
        }
        $queries = $this->params()->fromQuery();
        // get data
        $arrCodes = $this->entityCodes->fetchCountByDate($this->defineCompanyId, ['type' => 'exported_at', 'agent_id' => $id] + $queries);
        if(empty($arrCodes)) return true;
        // get product
        $optionProducts = $this->entityProducts->fetchAllOptions01($this->defineCompanyId);
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
        $path = "Thong-ke-xuat-kho-" . $this->defineCompanyId . "-" . date('Y-m-d-H-i-s') . ".xlsx";
        	
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
        ->setSize(12); /* Cài đặt font cho cả file */
        	
        /* Cài đặt chiều rộng cho từng ô */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        	
        /* Chỉnh dòng đầu tiên */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:D1'); /* Nhóm cột */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Thống kê xuất kho cho đại lý " . $valueCurrent['name']);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setSize(18)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_DARKGREEN ) );
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'STT')
        ->setCellValue('B2', 'Ngày xuất')
        ->setCellValue('C2', 'Sản phẩm')
        ->setCellValue('D2', 'Số lượng');
        $objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getFont()->setBold(true);
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        // set wrap text
        //$objPHPExcel->getActiveSheet()->getStyle('A10')->getAlignment()->setWrapText(true);
        // Truyền dữ liệu vào file
        $i = 3; // số bắt đầu
        $j = 1;
        foreach($arrCodes as $item){
            
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A" . $i, $j)
            ->setCellValue("B" . $i, $item['date_at'])
            ->setCellValue("C" . $i, $optionProducts[$item['product_id']])
            ->setCellValue("D" . $i, $item['total']);
            $i++;
            $j++;
        }
        //Zend_Debug::dump($arrMessage); die("PXT");
        unset($arrCodes);
        // Redirect output to a client’s web browser (Excel2007)
        ob_end_clean(); /* Xoá (hay loại bỏ) bất kỳ khoảng trắng trong cặp lệnh này*/
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$path.'"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        //$objWriter->save(FILES_UPLOAD . $path);
        $objWriter->save('php://output');
    
        ob_end_clean();
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        return true;
    }

    public function exportExcelDateAction(){
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        // check import
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityAgents->fetchRow($id);
        if(empty($valueCurrent)){
            die('Not found!');
        }
        $queries = $this->params()->fromQuery();
        // get data
        $arrCodes = $this->entityCodes->fetchDetailByDate($this->defineCompanyId, ['type' => 'exported_at', 'agent_id' => $id, 'date_at' => $queries['date_at'], 'product_id' => $queries['product_id']]);
        if(empty($arrCodes)) return true;
        // get product
        $optionProducts = $this->entityProducts->fetchAllOptions01($this->defineCompanyId);
        // get storehouse
        $optionStorehouses = $this->entityStorehouses->fetchAllOptions(['company_id' => $this->defineCompanyId]);
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
        $path = "Thong-ke-xuat-kho-chi-tiet-" . $this->defineCompanyId . "-ngay-" . $queries['date_at'] . ".xlsx";
        	
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
        ->setSize(12); /* Cài đặt font cho cả file */
        	
        /* Cài đặt chiều rộng cho từng ô */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        	
        /* Chỉnh dòng đầu tiên */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:E1'); /* Nhóm cột */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Thống kê xuất kho cho đại lý " . $valueCurrent['name'] . " ngày " . $queries['date_at'] . "");
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setSize(18)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_DARKGREEN ) );
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'STT')
        ->setCellValue('B2', 'Số serial')
        ->setCellValue('C2', 'Sản phẩm')
        ->setCellValue('D2', 'Kho')
        ->setCellValue('E2', 'Thời gian xuất');
        $objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getFont()->setBold(true);
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        // set wrap text
        //$objPHPExcel->getActiveSheet()->getStyle('A10')->getAlignment()->setWrapText(true);
        // Truyền dữ liệu vào file
        $i = 3; // số bắt đầu
        $j = 1;
        foreach($arrCodes as $item){
            
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A" . $i, $j)
            ->setCellValue("B" . $i, ("'" . $item['serial']))
            ->setCellValue("C" . $i, $optionProducts[$item['product_id']])
            ->setCellValue("D" . $i, $optionStorehouses[$item['storehouse_id']])
            ->setCellValue("E" . $i, $item['exported_at']);
            $i++;
            $j++;
        }
        //Zend_Debug::dump($arrMessage); die("PXT");
        unset($arrCodes);
        // Redirect output to a client’s web browser (Excel2007)
        ob_end_clean(); /* Xoá (hay loại bỏ) bất kỳ khoảng trắng trong cặp lệnh này*/
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$path.'"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        //$objWriter->save(FILES_UPLOAD . $path);
        $objWriter->save('php://output');
    
        ob_end_clean();
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        return true;
    }
}
