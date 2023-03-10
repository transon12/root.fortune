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
            $this->flashMessenger()->addWarningMessage('C?? l???i trong qu?? tr??nh xu???t d??? li???u, ????? ngh??? li??n h??? Admin!');
            return $this->redirect()->toRoute('statistics/index');
        }
        // ?????t t??n file
        $path = "Thong-ke-tin-nhan-" . COMPANY_ID . "-" . date('Y-m-d-H-i-s') . ".xlsx";

        /* T???o m???i m???t ?????i t?????ng PHPExcel */
        $objPHPExcel = new \PHPExcel();
        /* C??i ?????t Properties */
        $objPHPExcel->getProperties()->setCreator("Pxt Creator")
            ->setLastModifiedBy("ldc5vn Modified")
            ->setTitle("ldc5vn Title")
            ->setSubject("ldc5vn Subject")
            ->setDescription("ldc5vn Description")
            ->setKeywords("ldc5vn Keywords")
            ->setCategory("ldc5vn Category");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')
            ->setSize(12); /* C??i ?????t font cho c??? file */

        /* C??i ?????t chi???u r???ng cho t???ng ?? */
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
		

        /* Ch???nh d??ng ?????u ti??n */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:L1'); /* Nh??m c???t */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Th???ng k?? tin nh???n (" . date('d/m/Y H:i:s') . ")");
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setSize(18)
            ->setBold(true)
            ->setColor(new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_DARKGREEN));

        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', 'STT')
            ->setCellValue('B2', 'H??nh th???c')
            ->setCellValue('C2', 'Serial')
            ->setCellValue('D2', 'S???n ph???m')
            ->setCellValue('E2', 'S??? ??i???n tho???i')
            ->setCellValue('F2', 'M?? PIN')
            ->setCellValue('G2', 'Tin nh???n ?????n')
			->setCellValue('H2', 'Kh??ch ??i???n')
			->setCellValue('I2', 'T???nh th??nh')
			->setCellValue('J2', 'T??n ?????i l??')
		    ->setCellValue('K2', 'Tin nh???n tr??? ra')
            ->setCellValue('L2', 'Th???i gian nh???n');
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
        // Truy???n d??? li???u v??o file
        $i = 3; // s??? b???t ?????u
        $j = 1;
        foreach ($arrMessages as $item) {

            $type = '';
            // $info = '';

            if ($item['type'] == '1') {
                $type = "SMS";
                // $info .= "- M?? PIN: " . $item['code_id'] . "\n- ";
                // $info .= "Tn ?????n: " . $item['message_in'] . "\n- ";
                // $info .= "Tn tr??? ra: " . $item['message_out'] . "";
            } elseif($item['type'] == '2') {
                $type = "Website";
                // $info .= "- M?? PIN: " . $item['code_id'] . "\n- ";
                // $info .= "Tn tr??? ra: " . $item['message_out'] . "";
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
        // Redirect output to a client???s web browser (Excel2007)
        ob_end_clean(); /* Xo?? (hay lo???i b???) b???t k??? kho???ng tr???ng trong c???p l???nh n??y*/
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
            $this->flashMessenger()->addWarningMessage('C?? l???i trong qu?? tr??nh xu???t d??? li???u, ????? ngh??? li??n h??? Admin!');
            return $this->redirect()->toRoute('statistics/index');
        }
        // ?????t t??n file
        $path = "Thong-ke-tin-nhan-" . COMPANY_ID . "-" . date('Y-m-d-H-i-s') . ".xlsx";

        /* T???o m???i m???t ?????i t?????ng PHPExcel */
        $objPHPExcel = new \PHPExcel();
        /* C??i ?????t Properties */
        $objPHPExcel->getProperties()->setCreator("Pxt Creator")
            ->setLastModifiedBy("ldc5vn Modified")
            ->setTitle("ldc5vn Title")
            ->setSubject("ldc5vn Subject")
            ->setDescription("ldc5vn Description")
            ->setKeywords("ldc5vn Keywords")
            ->setCategory("ldc5vn Category");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')
            ->setSize(12); /* C??i ?????t font cho c??? file */

        /* C??i ?????t chi???u r???ng cho t???ng ?? */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(100);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);

        /* Ch???nh d??ng ?????u ti??n */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:G1'); /* Nh??m c???t */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Th???ng k?? tin nh???n (" . date('d/m/Y H:i:s') . ")");
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->setSize(18)
            ->setBold(true)
            ->setColor(new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_DARKGREEN));

        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', 'STT')
            ->setCellValue('B2', 'Serial')
            ->setCellValue('C2', 'S???n ph???m')
            ->setCellValue('D2', 'S??? ??i???n tho???i')
            ->setCellValue('E2', 'M?? ?????i l??')
            ->setCellValue('F2', 'Th??ng tin')
            ->setCellValue('G2', 'Th???i gian nh???n');
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
        // Truy???n d??? li???u v??o file
        $i = 3; // s??? b???t ?????u
        $j = 1;
        foreach ($arrMessages as $item) {

            $type = '';
            $info = '';

            if ($item['type'] == '0') {
                $type = "SMS";
                $info .= "- M?? PIN: " . $item['code_id'] . "\n- ";
                $info .= "Tn ?????n: " . $item['message_in'] . "\n- ";
                $info .= "Tn tr??? ra: " . $item['message_out'] . "";
            } else {
                $type = "Qu??t QRCode";
                $contentIn = json_decode($item['content_in'], true);
                if (isset($contentIn['fullname'])) {
                    if ($info != '') {
                        $info .= "\n";
                    }
                    $info .= "H??? v?? t??n: " . $contentIn['fullname'];
                }
                if (isset($contentIn['name_city'])) {
                    if ($info != '') {
                        $info .= "\n";
                    }
                    $info .= "Khu v???c: " . $contentIn['name_city'];
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
        // Redirect output to a client???s web browser (Excel2007)
        ob_end_clean(); /* Xo?? (hay lo???i b???) b???t k??? kho???ng tr???ng trong c???p l???nh n??y*/
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
