<?php

namespace Statistics\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Storehouses\Model\Products;
use Storehouses\Model\Agents;
use Codes\Model\Codes;
use Settings\Model\Settings;
use Settings\Model\Messages;
use Statistics\Form\Index\SearchForm;
use Statistics\Model\TableStatistics;

class IndexController extends AdminCore
{

    public $entitySettings;
    public $entityCodes;
    public $entityProducts;
	private $entityAgents;
    public $entityMessages;
    public $entityTableStatistics;

    public function __construct(
        PxtAuthentication $entityPxtAuthentication,
        Settings $entitySettings,
        Codes $entityCodes,
        Products $entityProducts,
		Agents $entityAgents,
        Messages $entityMessages,
        TableStatistics $entityTableStatistics
    ) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityCodes = $entityCodes;
        $this->entityProducts = $entityProducts;
		$this->entityAgents = $entityAgents;
        $this->entityMessages = $entityMessages;
        $this->entityTableStatistics = $entityTableStatistics;
    }

    public function indexAction()
    {
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        if (isset($queries['btnExport'])) {
            $this->exportIndex($queries);
        }
        // $arrMessages = new Paginator(new ArrayAdapter($this->entityMessages->fetchAlls($queries + ['company_id' => COMPANY_ID])));

        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // \Zend\Debug\Debug::dump($contentPaginator); die();
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;

        $arrMessages = $this->entityMessages->fetchAlls($queries + [
        'offset' => ((int)$page - 1), 
        'limit' => $perPage
    ]);

        // \Zend\Debug\Debug::dump($arrMessages); die();
        // $arrMessages->setCurrentPageNumber($page);
        // $arrMessages->setItemCountPerPage($perPage);
        // $arrMessages->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrMessages'       => $arrMessages,
            'contentPaginator'  => $contentPaginator,
            'totalRow'          => $this->entityMessages->fetchCountAll($queries + ['company_id' => COMPANY_ID]),
            'page'              => $page,
            'perPage'           => $perPage,
            'formSearch'        => $formSearch,
            'queries'           => $queries,
            'optionProducts'    => $this->entityProducts->fetchAllOptions01(['company_id' => COMPANY_ID]),
			'optionAgents'    => $this->entityAgents->fetchAllToOptions(['company_id' => COMPANY_ID])
        ]);
    }

    public function exportIndex($queries = null)
    {
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        // get data
        $arrMessages = $this->entityMessages->fetchAlls($queries + ['company_id' => COMPANY_ID]);
        if (empty($arrMessages)) return true;
        $optionProducts = $this->entityProducts->fetchAllOptions01(['company_id' => COMPANY_ID]);
		$optionAgents = $this->entityAgents->fetchAllOptions01(['company_id' => COMPANY_ID]);
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        } else {
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xuất dữ liệu, đề nghị liên hệ Admin!');
            return $this->redirect()->toRoute('statistics/index');
        }
        // Đặt tên file
        $path = "Thong-ke-tin-nhan-" . COMPANY_ID . "-" . date('Y-m-d-H-i-s') . ".xlsx";

        /* Tạo mới một đối tượng PHPExcel */
        $objPHPExcel = new \PHPExcel();
        /* Cài đặt Properties */
        $objPHPExcel->getProperties()->setCreator("Pxt Creator")
            ->setLastModifiedBy("ldc5vn Modified")
            ->setTitle("ldc5vn Title")
            ->setSubject("ldc5vn Subject")
            ->setDescription("ldc5vn Description")
            ->setKeywords("ldc5vn Keywords")
            ->setCategory("ldc5vn Category");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')
            ->setSize(12); /* Cài đặt font cho cả file */

        /* Cài đặt chiều rộng cho từng ô */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(100);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(25);
		

        /* Chỉnh dòng đầu tiên */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:L1'); /* Nhóm cột */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Thống kê tin nhắn (" . date('d/m/Y H:i:s') . ")");
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setSize(18)
            ->setBold(true)
            ->setColor(new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_DARKGREEN));

        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', 'STT')
            ->setCellValue('B2', 'Hình thức')
            ->setCellValue('C2', 'Serial')
            ->setCellValue('D2', 'Sản phẩm')
            ->setCellValue('E2', 'Số điện thoại')
            ->setCellValue('F2', 'Mã PIN')
            ->setCellValue('G2', 'Tin nhắn đến')
			->setCellValue('H2', 'Khách điền')
			->setCellValue('I2', 'Tỉnh thành')
			->setCellValue('J2', 'Tên đại lý')
		    ->setCellValue('K2', 'Tin nhắn trả ra')
            ->setCellValue('L2', 'Thời gian nhắn');
        $objPHPExcel->getActiveSheet()->getStyle('A2:I2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:I2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:I2')->getFont()->setBold(true);
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('I')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
		$objPHPExcel->getActiveSheet()->getStyle('J')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
		$objPHPExcel->getActiveSheet()->getStyle('K')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
		$objPHPExcel->getActiveSheet()->getStyle('L')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
		// set wrap text
        //$objPHPExcel->getActiveSheet()->getStyle('A10')->getAlignment()->setWrapText(true);
        // Truyền dữ liệu vào file
        $i = 3; // số bắt đầu
        $j = 1;
        foreach ($arrMessages as $item) {

            $type = '';
            // $info = '';

            if ($item['type'] == '1') {
                $type = "SMS";
                // $info .= "- Mã PIN: " . $item['code_id'] . "\n- ";
                // $info .= "Tn đến: " . $item['message_in'] . "\n- ";
                // $info .= "Tn trả ra: " . $item['message_out'] . "";
            } elseif($item['type'] == '2') {
                $type = "Website";
                // $info .= "- Mã PIN: " . $item['code_id'] . "\n- ";
                // $info .= "Tn trả ra: " . $item['message_out'] . "";
            }
			 $contentIn = json_decode($item['content_in'], true);

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue("A" . $i, $j)
                ->setCellValue("B" . $i, $type)
                ->setCellValue("C" . $i, $item['code_serial'])
                ->setCellValue("D" . $i, (isset($optionProducts[$item['product_id']]) ? $optionProducts[$item['product_id']] : ""))
                ->setCellValue("E" . $i, $item['phone_id'])
                ->setCellValue("F" . $i, $item['code_id'])
                ->setCellValue("G" . $i, $item['message_in'])
				->setCellValue("H" . $i, (isset($contentIn['type_agent']) ? $contentIn['type_agent'] : ""))
				->setCellValue("I" . $i, (isset($contentIn['name_city']) ? $contentIn['name_city'] : ""))
				->setCellValue("J" . $i, (isset($optionAgents[$item['agent_id']]) ? $optionAgents[$item['agent_id']] : ""))
				->setCellValue("K" . $i, $item['message_out'])
                ->setCellValue("L" . $i, $item['created_at']);
            $i++;
            $j++;
        }
        //Zend_Debug::dump($arrMessage); die("PXT");
        unset($arrMessage);
        // Redirect output to a client’s web browser (Excel2007)
        ob_end_clean(); /* Xoá (hay loại bỏ) bất kỳ khoảng trắng trong cặp lệnh này*/
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $path . '"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        //$objWriter->save(FILES_UPLOAD . $path);
        $objWriter->save('php://output');

        ob_end_clean();
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        return true;
    }

    public function exportIndex1($queries = null)
    {
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        // get data
        $arrMessages = $this->entityMessages->fetchAlls($queries + ['company_id' => COMPANY_ID]);
        if (empty($arrMessages)) return true;
        $optionProducts = $this->entityProducts->fetchAllOptions01(['company_id' => COMPANY_ID]);
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        } else {
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xuất dữ liệu, đề nghị liên hệ Admin!');
            return $this->redirect()->toRoute('statistics/index');
        }
        // Đặt tên file
        $path = "Thong-ke-tin-nhan-" . COMPANY_ID . "-" . date('Y-m-d-H-i-s') . ".xlsx";

        /* Tạo mới một đối tượng PHPExcel */
        $objPHPExcel = new \PHPExcel();
        /* Cài đặt Properties */
        $objPHPExcel->getProperties()->setCreator("Pxt Creator")
            ->setLastModifiedBy("ldc5vn Modified")
            ->setTitle("ldc5vn Title")
            ->setSubject("ldc5vn Subject")
            ->setDescription("ldc5vn Description")
            ->setKeywords("ldc5vn Keywords")
            ->setCategory("ldc5vn Category");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')
            ->setSize(12); /* Cài đặt font cho cả file */

        /* Cài đặt chiều rộng cho từng ô */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(100);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);

        /* Chỉnh dòng đầu tiên */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:G1'); /* Nhóm cột */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Thống kê tin nhắn (" . date('d/m/Y H:i:s') . ")");
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->setSize(18)
            ->setBold(true)
            ->setColor(new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_DARKGREEN));

        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', 'STT')
            ->setCellValue('B2', 'Serial')
            ->setCellValue('C2', 'Sản phẩm')
            ->setCellValue('D2', 'Số điện thoại')
            ->setCellValue('E2', 'Mã đại lý')
            ->setCellValue('F2', 'Thông tin')
            ->setCellValue('G2', 'Thời gian nhắn');
        $objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getFont()->setBold(true);
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        // set wrap text
        //$objPHPExcel->getActiveSheet()->getStyle('A10')->getAlignment()->setWrapText(true);
        // Truyền dữ liệu vào file
        $i = 3; // số bắt đầu
        $j = 1;
        foreach ($arrMessages as $item) {

            $type = '';
            $info = '';

            if ($item['type'] == '0') {
                $type = "SMS";
                $info .= "- Mã PIN: " . $item['code_id'] . "\n- ";
                $info .= "Tn đến: " . $item['message_in'] . "\n- ";
                $info .= "Tn trả ra: " . $item['message_out'] . "";
            } else {
                $type = "Quét QRCode";
                $contentIn = json_decode($item['content_in'], true);
                if (isset($contentIn['fullname'])) {
                    if ($info != '') {
                        $info .= "\n";
                    }
                    $info .= "Họ và tên: " . $contentIn['fullname'];
                }
                if (isset($contentIn['name_city'])) {
                    if ($info != '') {
                        $info .= "\n";
                    }
                    $info .= "Khu vực: " . $contentIn['name_city'];
                }
            }

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue("A" . $i, $j)
                ->setCellValue("B" . $i, $item['code_serial'])
                ->setCellValue("C" . $i, (isset($optionProducts[$item['product_id']]) ? $optionProducts[$item['product_id']] : ""))
                ->setCellValue("D" . $i, $item['phone_id'])
                ->setCellValue("E" . $i, $item['code_agent'])
                ->setCellValue("F" . $i, $info)
                ->setCellValue("G" . $i, $item['created_at']);
            $i++;
            $j++;
        }
        //Zend_Debug::dump($arrMessage); die("PXT");
        unset($arrMessage);
        // Redirect output to a client’s web browser (Excel2007)
        ob_end_clean(); /* Xoá (hay loại bỏ) bất kỳ khoảng trắng trong cặp lệnh này*/
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $path . '"');
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
