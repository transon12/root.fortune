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
use Storehouses\Form\Imports\ImportsForm;
use Settings\Model\Settings;
use Storehouses\Model\Products;
use Codes\Model\Codes;
use Storehouses\Model\Storehouses;

class ImportsController extends AdminCore{
    
    public $entityStorehouses;
    public $entitySettings;
    public $entityProducts;
    public $entityCodes;
    
    public function __construct(Storehouses $entityStorehouses, PxtAuthentication $entityPxtAuthentication, 
        Settings $entitySettings, Products $entityProducts, Codes $entityCodes) {
        parent::__construct($entityPxtAuthentication);
        $this->entityStorehouses = $entityStorehouses;
        $this->entitySettings = $entitySettings;
        $this->entityProducts = $entityProducts;
        $this->entityCodes = $entityCodes;
    }
    
    public function importsAction(){
        // check import
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityStorehouses->fetchRow($id);
        if(empty($valueCurrent)){
            die('Not found!');
        }
        // call iframe
        $this->layout()->setTemplate('iframe/layout');

        $settingDatas = $this->entitySettings->fetchFormImports($this->defineCompanyId);
        // \Zend\Debug\Debug::dump($settingDatas); die();
        
        $form = new ImportsForm($settingDatas);

        
        // set product_id
        $optionProducts = $this->entityProducts->fetchAllOptions01(['company_id' => $this->defineCompanyId]);
        $form->get('products_id')->setValueOptions( ['' => '------- Chọn một sản phẩm -------'] + $optionProducts );

        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            //\Zend\Debug\Debug::dump($valuePost); die();
            if(isset($valuePost['btnExport'])){
                $this->importExcelAction();
            }
            $form->setData($valuePost);
            $status = (int)$valuePost['status'];
            if($form->isValid() || $status === 0 ){
                if($status === 0){
                    $form->get('products_id')->setMessages([]);
                    $form->get('datetime_import')->setMessages([]);
                }
                // check serial or qrcode
                /**
                 * $isSerial = 1; ~ is qrcode
                 * $isSerial = 2; ~ only one serial
                 * $isSerial = 3; ~ serial begin and serial end
                 * $isSerial = 0; ~ id
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
                $arrCodes = $this->entityCodes->fetchAlls($this->defineCompanyId, ['imports' => '1', 'is_all' => '1'] + $options);
                if(empty($arrCodes) || $status === 0 ){
                    $valuePost['datetime_import'] = (isset($valuePost['datetime_import']) && strlen($valuePost['datetime_import']) > 0) ? $valuePost['datetime_import'] : time();
                    $importedAt = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_import']);
                    $data = [
                        'storehouse_id' => ($status === 0) ? null : $id,
                        'imported_at' => ($status === 0) ? null : date_format($importedAt, 'Y-m-d H:i:s'),
                        'product_id' => ($status === 0) ? null : $valuePost['products_id'],
                        'agent_id' => null,
                        'exported_at' => null,
                        'number_checked' => 0,
                        'number_checked_qrcode' => 0,
                        'phone_id' => null,
                        'checked_at' => null
                    ];
                    if(!empty($settingDatas)){
                        foreach($settingDatas as $key => $item){
                            $data[$key] = ($status == 1) ? $valuePost[$key] : null;
                        }
                    }
                    $result = $this->entityCodes->updatesRowAsCondition($this->defineCompanyId, ['data' => $data] + $options);
                    if($result > 0){
                        if($status === 0){
                            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
                        }else{
                            $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                        }
                    }else{
                        if($options['is_serial'] == 3){
                            $this->flashMessenger()->addWarningMessage('Không tìm thấy chuỗi này!');
                        }else{
                            $this->flashMessenger()->addWarningMessage('Không tìm thấy tem này!');
                        }
                    }
                }else{
                    if($options['is_serial'] == 3){
                        $this->flashMessenger()->addWarningMessage('Trong chuỗi này đã có tem được thêm vào trước đó!');
                    }else{
                        $this->flashMessenger()->addWarningMessage('Tem này này đã được thêm vào trước đó!');
                    }
                }
                $form->get('codes')->setValue('');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        //\Zend\Debug\Debug::dump($settingDatas);die();
        return new ViewModel([
            'arrCodes'              => $this->entityCodes->fetchCountByDate($this->defineCompanyId, ['type' => 'imported_at', 'storehouse_id' => $id]), 
            //'arrCodesNotExport'   => $this->entityCodes->fetchCountByDateNotExport(['company_id' => $this->defineCompanyId, 'storehouse_id' => $id]), 
            'form'                  => $form,
            'optionProducts'        => $optionProducts,
            'settingDatas'          => $settingDatas,
            'optionProductsDetail'  => $this->entityProducts->fetchAllOptions03(['company_id' => $this->defineCompanyId]),
            'id'                    => $id
            //'contentPaginator'    => $contentPaginator, 
            //'dialId'              => $dialId
        ]);
    }

    public function importExcelAction(){
        // check import
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityStorehouses->fetchRow($id);
        
        if(empty($valueCurrent)){
            die('Not found!');
        }
        
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xuất dữ liệu, đề nghị liên hệ Admin!');
            return $this->redirect()->toRoute('storehouses/imports', ['action' => 'imports', 'id' => $id]);
        }
        
        $arrCodes = $this->entityCodes->fetchCountByDate($this->defineCompanyId, ['type' => 'imported_at', 'storehouse_id' => $id]);
        $arrCodesNotExport = $this->entityCodes->fetchCountByDateNotExport(['company_id' => $this->defineCompanyId, 'storehouse_id' => $id]);
        $optionProductsDetail = $this->entityProducts->fetchAllOptions03(['company_id' => $this->defineCompanyId]);
        
        // Đặt tên file
        $path = "Thong-ke-nhap-kho-" . \Pxt\String\ChangeString::deleteAccented( $valueCurrent['name'] ) . "-" . date('Y-m-d-H-i-s') . ".xlsx";
        	
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        	
        /* Chỉnh dòng đầu tiên */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1'); /* Nhóm cột */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Thống kê nhập kho (" . date('d/m/Y H:i:s') . ")");
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setSize(18)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_DARKGREEN ) );
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'STT')
        ->setCellValue('B2', 'Ngày nhập')
        ->setCellValue('C2', 'Mã sản phẩm')
        ->setCellValue('D2', 'Tên sản phẩm')
        ->setCellValue('E2', 'Tổng nhập')
        ->setCellValue('F2', 'Tồn kho');
        $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getFont()->setBold(true);
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        // set wrap text
        //$objPHPExcel->getActiveSheet()->getStyle('A10')->getAlignment()->setWrapText(true);
        // Truyền dữ liệu vào file
        $i = 3; // số bắt đầu
        $j = 1;
        foreach($arrCodes as $item){
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A" . $i, $j)
            ->setCellValue("B" . $i, $item['date_at'])
            ->setCellValue("C" . $i, (isset($optionProductsDetail[$item['product_id']]['code']) ? $optionProductsDetail[$item['product_id']]['code'] : ""))
            ->setCellValue("D" . $i, (isset($optionProductsDetail[$item['product_id']]['name']) ? $optionProductsDetail[$item['product_id']]['name'] : ""))
            ->setCellValue("E" . $i, $item['total'])
            ->setCellValue("F" . $i, (isset($arrCodesNotExport[$item['date_at'] . '-' . $item['product_id']]) ? $arrCodesNotExport[$item['date_at'] . '-' . $item['product_id']] : "0"));
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

    public function exportInventoryAction(){
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        // check import
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityStorehouses->fetchRow($id);
        if(empty($valueCurrent)){
            die('Not found!');
        }
        $queries = $this->params()->fromQuery();
        // get data
        $arrCodes = $this->entityCodes->fetchDetailByDateNotExport(['company_id' => $this->defineCompanyId, 'storehouse_id' => $id, 'product_id' => $queries['product_id'], 'date_at' => $queries['date_at']]);
        if(empty($arrCodes)){
            $this->flashMessenger()->addWarningMessage('Tồn kho đã hết nên không thể xuất!');
            return $this->redirect()->toRoute('storehouses/imports', ['action' => 'index', 'id' => $id]);
        }
        // get product
        $optionProductDetails = $this->entityProducts->fetchAllOptions03(['company_id' => $this->defineCompanyId]);
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xuất dữ liệu, đề nghị liên hệ Admin!');
            return $this->redirect()->toRoute('agents/index');
        }
        // Đặt tên file
        $path = "Thong-ke-ton-kho-" . $this->defineCompanyId . "-" . $optionProductDetails[$queries['product_id']]['code'] . "-" . $queries['date_at'] . ".xlsx";
        	
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        	
        /* Chỉnh dòng đầu tiên */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:E1'); /* Nhóm cột */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Thống kê tồn kho sản phẩm " . $optionProductDetails[$queries['product_id']]['name'] . " ngày " . $queries['date_at'] . "");
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setSize(18)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_DARKGREEN ) );
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'STT')
        ->setCellValue('B2', 'Mã sản phẩm')
        ->setCellValue('C2', 'Tên sản phẩm')
        ->setCellValue('D2', 'Số serial')
        ->setCellValue('E2', 'Thời gian nhập kho');
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
            ->setCellValue("B" . $i, $optionProductDetails[$item['product_id']]['code'])
            ->setCellValue("C" . $i, $optionProductDetails[$item['product_id']]['name'])
            ->setCellValue("D" . $i, ("'" . $item['serial']))
            ->setCellValue("E" . $i, $item['imported_at']);
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
