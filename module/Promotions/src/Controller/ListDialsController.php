<?php
namespace Promotions\Controller;

use Zend\View\Model\ViewModel;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Promotions\Model\Promotions;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Settings\Model\Settings;
use Promotions\Model\ListDials;
use Promotions\Form\ListDials\AddForm;
use Promotions\Form\ListDials\DeleteForm;
use Promotions\Form\ListDials\SearchForm;
use Zend\Filter\File\RenameUpload;
use Settings\Model\Phones;
use Promotions\Model\WinnerDials;
use Promotions\Form\ListDials\AddFileForm;

class ListDialsController extends AdminCore{
    private $entityPromotions;
    private $entitySettings;
    private $entityListDials;
    private $entityPhones;
    private $entityWinnerDials;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, Promotions $entityPromotions, 
        ListDials $entityListDials, Phones $entityPhones, WinnerDials $entityWinnerDials) {
        parent::__construct($entityPxtAuthentication);
        $this->entityPromotions = $entityPromotions;
        $this->entitySettings = $entitySettings;
        $this->entityListDials = $entityListDials;
        $this->entityPhones = $entityPhones;
        $this->entityWinnerDials = $entityWinnerDials;
    }
    
    public function indexAction(){
        $promotionId = (int)$this->params()->fromRoute('id', 0);
        $this->checkPromotionsId($promotionId);
        
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        
        $request = $this->getRequest();
        
        $this->layout()->setTemplate('iframe/layout');
        $arrListDials = new Paginator(new ArrayAdapter( $this->entityListDials->fetchAlls([
                'promotion_id' => $promotionId, 
                'keyword' => isset($queries['keyword']) ? $queries['keyword'] : ''
            ]) 
        ));
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
            'promotionId' => $promotionId,
            'formSearch' => $formSearch,
            'queries' => $queries
        ]);
    }
    
    public function addAction(){
        $promotionId = (int)$this->params()->fromRoute('id', 0);
        $this->checkPromotionsId($promotionId);
        $this->layout()->setTemplate('iframe/layout');
        $view = new ViewModel();
        $form = new AddForm();
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'phone_id' => \Pxt\String\ChangeString::convertPhoneVn($valuePost['phones_id']),
                    'code_id' => $valuePost['codes_id'],
                    'promotion_id' => $promotionId,
                    'status' => $valuePost['status'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entityListDials->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                return $this->redirect()->toRoute('promotions/list-dials', ['action' => 'index', 'id' => $promotionId]);
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $view->setVariable('form', $form);
        $view->setVariable('promotionId', $promotionId);
        return $view;
    }
    
    public function addFileAction(){
        $promotionId = (int)$this->params()->fromRoute('id', 0);
        $this->checkPromotionsId($promotionId);
        $this->layout()->setTemplate('iframe/layout');
        $view = new ViewModel();
        $form = new AddFileForm();
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
           // \Zend\Debug\Debug::dump($valuePost); die();
            if(isset($valuePost['btnSample'])){
                $this->exportSampleAction();
            }elseif(isset($valuePost['btnImport'])){
                $form->setData($valuePost);
                if($form->isValid()){
                    $file = $request->getFiles()->toArray();
                    //upload background
                    if($file['file_import']['name'] != ''){
                        $url = $this->uploadFile($file, 'file_import');
                        if($this->importPhones($url, $promotionId)){
                            $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                            return $this->redirect()->toRoute('promotions/list-dials', ['action' => 'index', 'id' => $promotionId]);
                        }else{
                            $this->flashMessenger()->addWarningMessage('File có vấn đề, đề nghị kiểm tra lại!');
                        }
                    }
                }
            }
        }
        $view->setVariable('form', $form);
        $view->setVariable('promotionId', $promotionId);
        return $view;
    }
    
    public function checkPromotionsId($id = 0){
        $valueCurrent = $this->entityPromotions->fetchRow($id);
        if(empty($valueCurrent)){
            die('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
        }
        return true;
    }
    
    public function deleteAction(){
        $promotionId = (int)$this->params()->fromRoute('id', 0);
        $this->checkPromotionsId($promotionId);
        
        $this->layout()->setTemplate('empty/layout');
        
        $request = $this->getRequest();
        $listDialId = (int)$this->params()->fromRoute('list_dials_id', 0);
        $valueCurrent = $this->entityListDials->fetchRow($listDialId);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('promotions/list-dials');
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];
        $countWinnerDials = $this->entityWinnerDials->fetchCount(['list_dial_id' => $listDialId]);
        if($countWinnerDials > 0){
            $checkRelationship['list_dials'] = 1;
        }

        if($request->isPost()){
            // delete all dials promotions
            if($countWinnerDials > 0){
                $this->entityWinnerDials->deleteRows(['list_dial_id' => $listDialId]);
            }
            // delete list dial
            $this->entityListDials->deleteRow($listDialId);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form,
            'checkRelationship' => $checkRelationship, 
            'valueCurrent' => $valueCurrent
        ]);
    }

    public function exportSampleAction(){
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xuất dữ liệu, đề nghị liên hệ Admin!');
            return $this->redirect()->toRoute('promotions/index');
        }
        // Đặt tên file
        $path = "Mau-nhap-danh-sach-tham-gia-quay-so.xlsx";
        	
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        	
        /* Chỉnh dòng đầu tiên */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:D1'); /* Nhóm cột */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Mẫu nhập danh sách tham gia quay số");
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_DARKGREEN ) );
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'Mã trúng thưởng')
        ->setCellValue('B2', 'Số điện thoại')
        ->setCellValue('C2', 'Họ và tên đệm')
        ->setCellValue('D2', 'Tên');
        $objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:D2')->getFont()->setBold(true);
        
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
	
	/**
	 * import data
	 */
	function importPhones($url = "", $promotionId){
		if($url == ""){
		    return false;
		}
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xuất dữ liệu, đề nghị liên hệ Admin!');
            return $this->redirect()->toRoute('promotions/list-dials', ['id' => $promotionId]);
        }
		
		// Load nội dung file lên để xử lý
		try {
			$objPHPExcel = \PHPExcel_IOFactory::load(FILES_UPLOAD . $url);
		} catch(Exception $e) {
			die("Lỗi load thư viện '".pathinfo($url, PATHINFO_BASENAME) . "': " . $e->getMessage());
		}
		
		// Lấy dữ liệu tại sheet mặc định
		$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		//die('abc');
		$n = 1;
		if(!empty($sheetData)){
			foreach($sheetData as $itemSheet){
				if($n >= 3){
				    if(strlen($itemSheet['A']) > 0 && strlen($itemSheet['B']) > 0){
				        // check phone exist
    				    $phone = \Pxt\String\ChangeString::convertPhoneVn($itemSheet['B']);
				        $currentPhone = $this->entityPhones->fetchRow($phone);
				        if(empty($currentPhone)){
    				        $dataPhone = [];
    				        $dataPhone = [
    				            'id' => $phone,
    				            'company_id' => $this->defineCompanyId,
    				            'lastname' => $itemSheet['C'],
    				            'firstname' => $itemSheet['D'],
    				            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
    				        ];
    				        $this->entityPhones->addRow($dataPhone);
				        }else{
				            if($itemSheet['C'] != ''){
    				            $dataPhone = [];
        				        $dataPhone = [
        				            'lastname' => $itemSheet['C'],
        				            'firstname' => $itemSheet['D'],
        				        ];
        				        $this->entityPhones->updateRow($phone, $dataPhone);
				            }
				            
				        }
				        // add list dial
				        $dataListDials = [
				            'code_id' => $itemSheet['A'],
				            'phone_id' => $phone,
				            'promotion_id' => $promotionId,
    				        'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
				        ];
				        $this->entityListDials->addRow($dataListDials);
				    }
				}
				$n++;
			}
		}
		return true;
	}
    
}
