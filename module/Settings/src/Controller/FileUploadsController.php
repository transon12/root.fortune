<?php

namespace Settings\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Settings\Form\FileUploads\AddFileForm;
use Settings\Form\FileUploads\AddFolderForm;
use Settings\Form\Phones\AddForm;
use Settings\Form\Phones\EditForm;
use Settings\Form\Phones\DeleteForm;
use Settings\Model\Settings;
use Settings\Form\Phones\SearchForm;
use Settings\Form\Phones\ImportForm;
use Settings\Model\FileUploads;
use Zend\Filter\File\RenameUpload;

class FileUploadsController extends AdminCore{
    public $entityFileUploads;
    public $entitySettings;
    public $entityAgents;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, FileUploads $entityFileUploads) {
        parent::__construct($entityPxtAuthentication);
        $this->entityFileUploads = $entityFileUploads;
        $this->entitySettings = $entitySettings;
    }
    
    public function indexAction(){
        $view = new ViewModel();
        $queries = $this->params()->fromQuery();
        $id = $this->params()->fromRoute('id', 0);
        // \Zend\Debug\Debug::dump($id);
        // \Zend\Debug\Debug::dump($queries);
        // die("ABCadfa");
        $iframe = isset($queries["iframe"]) ? 1 : 0;
        $valueCurrent = $this->entityFileUploads->fetchRow($id);
        
        if($id != "0" && empty($valueCurrent)){
            $this->redirect()->toRoute('settings/file-uploads');
        }
        $queries["parent_id"] = $id;
        //\Zend\Debug\Debug::dump($this->entityFileUploads->fetchAllRoot($id)); die();
        $arrFileUploads = new Paginator(new ArrayAdapter( $this->entityFileUploads->fetchAll($queries) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrFileUploads->setCurrentPageNumber($page);
        $arrFileUploads->setItemCountPerPage($perPage);
        $arrFileUploads->setPageRange($contentPaginator['page_range']);

        $view->setVariable("arrFileUploads", $arrFileUploads);
        $view->setVariable("contentPaginator", $contentPaginator);
        $view->setVariable("queries", $queries);
        $view->setVariable("id", $id);
        $view->setVariable("valueCurrent", $valueCurrent);
        $view->setVariable("arrRoot", $this->entityFileUploads->fetchAllRoot($id));

        // \Zend\Debug\Debug::dump($queries);
        // die("ABCadfa");
        if($iframe === 1){
            $this->layout()->setTemplate('iframe/layout');
            // $view->setTemplate('settings/file-uploads/index-modal');
        }

        return $view;
    }
    
    public function addFolderAction(){
        $id = $this->params()->fromRoute('id', 0);
        
        $valueCurrent = $this->entityFileUploads->fetchRow($id);
        
        if($id != "0" && empty($valueCurrent)){
            $this->redirect()->toRoute('settings/file-uploads');
        }
        // $urlRoot = explode("/", $valueCurrent["url"]);
        // \Zend\Debug\Debug::dump($urlRoot); die();

        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new AddFolderForm($request->getRequestUri());
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            
            $isValid = $form->isValid();
            if($isValid){
                $data = [
                    "company_id"    => \Admin\Service\Authentication::getCompanyId(),
                    "user_id"       => $this->sessionContainer->id,
                    "parent_id"     => $id,
                    "level"         => ($id == 0) ? 0 : ((int)$valueCurrent["level"] + 1),
                    "url"           => $this->uploadFolder( \Pxt\String\ChangeString::changeSlug($valuePost["name"]), $valueCurrent ),
                    "type"          => "folder",
                    "name"          => $valuePost["name"],
                    "created_at"    => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entityFileUploads->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
            //\Zend\Debug\Debug::dump($form->get('id')); die();
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        
        return new ViewModel(['form' => $form]);
    }

    private function uploadFolder($nameSlug = '', $valueCurrent = []){
        $arrUrl = [];
        if(!empty($valueCurrent)){
            $urlRoot = explode("/", $valueCurrent["url"]);
            if(!empty($urlRoot)){
                foreach($urlRoot as $itemRoot){
                    if(strlen(trim($itemRoot)) > 0){
                        $arrUrl[] = $itemRoot;
                    }
                }
            }
            //$arrUrl[] = $valueCurrent["url"];
            
        }else{
            $companyId = \Admin\Service\Authentication::getCompanyId();
            if($companyId == null || $companyId == ""){
                $arrUrl[] = "root";
            }else{
                $arrUrl[] = strtolower($companyId);
            }
        }  
        $arrUrl[] = $nameSlug;
        $url = "";
        $i = 0;
        foreach($arrUrl as $item){
            if($i != 0) $url .= "/";
            $url .= $item;
            //$urlUpload = "" . str_replace("/", "", URL_UPLOAD) . $url;
            $urlUpload = FILES_UPLOAD . $url;
            if( !file_exists($urlUpload) ){
                mkdir($urlUpload, 0777, true);
                //echo $urlUpload . " không tồn tại.\n";
			}else{
                //echo $urlUpload . " tồn tại.\n";
            }
            $i++;
        }
        //die("Da49 dung2");
        return $url;
    }
    
    public function addFileAction(){
        $id = $this->params()->fromRoute('id', 0);
        //die($id);
        
        $valueCurrent = $this->entityFileUploads->fetchRow($id);
        
        if($id != "0" && empty($valueCurrent)){
            $this->redirect()->toRoute('settings/file-uploads');
        }
        // $urlRoot = explode("/", $valueCurrent["url"]);
        // \Zend\Debug\Debug::dump($urlRoot); die();

        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new AddFileForm($request->getRequestUri());
        if($request->isPost()){
            $valuePost = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if($isValid){
                $file = $valuePost["file"];
                list($height, $width) = getimagesize($file["tmp_name"]);
                $extension = strtolower( pathinfo($file["name"], PATHINFO_EXTENSION) );
                // echo $height . "<br />";
                // echo $width . "<br />";
                // echo $extension . "<br />";
                // \Zend\Debug\Debug::dump($file);
                $reName = \Pxt\String\ChangeString::changeSlug($file["name"]);
                // echo $reName;
                // die();
                $data = [
                    "company_id"    => \Admin\Service\Authentication::getCompanyId(),
                    "user_id"       => $this->sessionContainer->id,
                    "parent_id"     => $id,
                    "level"         => ($id == 0) ? 0 : ((int)$valueCurrent["level"] + 1),
                    "url"           => $this->uploadFile( $file, $reName, $valueCurrent ),
                    "type"          => $file["type"],
                    "name"          => $reName,
                    "extension"     => $extension,
                    "height"        => $height,
                    "width"         => $width,
                    "size"          => $file["size"],
                    "created_at"    => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                //\Zend\Debug\Debug::dump($data); die();
                $this->entityFileUploads->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
            //\Zend\Debug\Debug::dump($form->get('id')); die();
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        
        return new ViewModel(['form' => $form]);
    }
    
    private function uploadFile($file = null, $reName, $valueCurrent){
        $arrUrl = [];
        if(!empty($valueCurrent)){
            $urlRoot = explode("/", $valueCurrent["url"]);
            if(!empty($urlRoot)){
                foreach($urlRoot as $itemRoot){
                    if(strlen(trim($itemRoot)) > 0){
                        $arrUrl[] = $itemRoot;
                    }
                }
            }
        }else{
            $companyId = \Admin\Service\Authentication::getCompanyId();
            if($companyId == null || $companyId == ""){
                $arrUrl[] = "root";
            }else{
                $arrUrl[] = strtolower($companyId);
            }
        }  
        $arrUrl[] = $reName;
        $url = "";
        $i = 0;
        foreach($arrUrl as $item){
            if($i != 0) $url .= "/";
            
            $urlUpload = FILES_UPLOAD . $url;
            if( !file_exists($urlUpload) ){
                //echo $urlUpload; die("zo");
                mkdir($urlUpload, 0777, true);
                //echo $urlUpload . " không tồn tại.\n";
			}
            $url .= $item;
            $i++;
        }

        // $info = pathinfo($file["file"]['name']);
        // $url = 'root/' . trim(\Pxt\String\ChangeString::changeSlug($info['filename'])) . '.' . strtolower( $info['extension'] );
        // $url3 = trim($url);
        // $url1 = 'root/' . str_replace(' ', '-', \Pxt\String\ChangeString::deleteAccented($info['filename'])) . '_' . time() . '.' . $info['extension'];
        // $url2 = 'root/abc.' . strtolower( $info['extension'] );
        $fileUpload = new RenameUpload([
             'target' => FILES_UPLOAD . $url,
            //'target' => FILES_UPLOAD . $url1,
            'overwrite'            => true,
        ]);
        $fileUpload->filter($file);
        return $url;
    }
    
    public function addAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new AddForm($request->getRequestUri());
        $arrAgents = $this->entityAgents->fetchAllToOptions();
        $form->get('agents_id')->setValueOptions( ['' => '------- Chọn một đại lý -------'] + $arrAgents );
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            // check phone was exist
            $isValid = $form->isValid();
            if($isValid && !empty( $this->entityPhones->fetchRow(\Pxt\String\ChangeString::convertPhoneVn($valuePost['id'])) )){
                $form->get('id')->setMessages($form->get('id')->getMessages() + ['phone_exist' => 'Số điện thoại này đã tồn tại!']);
                $isValid = false;
            }
            if($isValid){
                $data = [
                    'agents_id' => (isset($valuePost['agents_id']) ? $valuePost['agents_id'] : null),
                    'id' => \Pxt\String\ChangeString::convertPhoneVn($valuePost['id']),
                    'fullname' => $valuePost['fullname'],
                    'address' => $valuePost['address'],
                    'lock_time' => $valuePost['lock_time'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                    'locked_at' => ($valuePost['lock_time'] > 0) ? \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent() : null
                ];
                $this->entityPhones->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
            //\Zend\Debug\Debug::dump($form->get('id')); die();
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        
        return new ViewModel(['form' => $form]);
    }
    
    public function editAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = $this->params()->fromRoute('id', '');
        $valueCurrent = $this->entityPhones->fetchRow($id);
        
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('settings/phones');
        }else{
            $valuePost = $valueCurrent; 
        }
        
        $form = new EditForm($request->getRequestUri());
        $form->get('id')->setAttribute('readonly', true);
        
        $arrAgents = $this->entityAgents->fetchAllToOptions();
        $form->get('agents_id')->setValueOptions( ['' => '------- Chọn một đại lý -------'] + $arrAgents );
        
        $form->setData($valuePost);
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'agents_id' => (isset($valuePost['agents_id']) ? $valuePost['agents_id'] : null),
                    'fullname' => $valuePost['fullname'],
                    'address' => $valuePost['address'],
                    'lock_time' => $valuePost['lock_time'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                    'locked_at' => ($valuePost['lock_time'] > 0) ? \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent() : null
                ];
                $this->entityPhones->updateRow($id, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        
        return new ViewModel(['form' => $form]);
    }
    
    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = $this->params()->fromRoute('id', '');
        $valueCurrent = $this->entityPhones->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('settings/phones');
        }
        $form = new DeleteForm($request->getRequestUri());
        if($request->isPost()){
            $this->entityPhones->deleteRow($id);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel(['form' => $form]);
    }

    public function exportSample(){
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xuất dữ liệu, đề nghị liên hệ Admin!');
            return $this->redirect()->toRoute('settings/phones');
        }
        // Đặt tên file
        $path = "Mau-nhap-so-dien-thoai-dai-ly.xlsx";
        	
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        	
        /* Chỉnh dòng đầu tiên */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:D1'); /* Nhóm cột */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Mẫu nhập số điện thoại cho đại lý");
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_DARKGREEN ) );
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'Tên đại lý')
        ->setCellValue('B2', 'Mã đại lý')
        ->setCellValue('C2', 'Số điện thoại')
        ->setCellValue('D2', 'Tên người sở hữu số ĐT');
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
	
	/**
	 * import data
	 */
	function importPhones($url = "", $fileName){
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
            return $this->redirect()->toRoute('settings/phones');
        }
		
		// Load nội dung file lên để xử lý
		try {
			$objPHPExcel = \PHPExcel_IOFactory::load(FILES_UPLOAD . $url);
		} catch(Exception $e) {
			die("Lỗi load thư viện '".pathinfo($url, PATHINFO_BASENAME) . "': " . $e->getMessage());
		}
		
		// Lấy dữ liệu tại sheet mặc định
		$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		
		$n = 1;
		if(!empty($sheetData)){
			foreach($sheetData as $itemSheet){
				if($n >= 3){
				    if(strlen($itemSheet['B']) > 0 && strlen($itemSheet['C']) > 0){
				        // check code agent
    				    $currentAgent = $this->entityAgents->fetchRowCode($itemSheet['B']);
    				    $agentId = 0;
    				    if(empty($currentAgent)){
    				        $dataAgent = [];
    				        $dataAgent = [
    				            'name' => $itemSheet['A'],
    				            'code' => $itemSheet['B'],
                                'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
    				        ];
    				        $agentId = $this->entityAgents->addRow($dataAgent);
    				    }else{
    				        $agentId = $currentAgent['id'];
    				    }
    				    // check phone
    				    $phone = \Pxt\String\ChangeString::convertPhoneVn($itemSheet['C']);
    				    $currentPhone = $this->entityPhones->fetchRow($phone);
    				    if(empty($currentPhone)){
    				        $dataPhone = [];
    				        $dataPhone = [
    				            'id' => $phone,
    				            'agents_id' => $agentId,
    				            'fullname' => $itemSheet['D'],
    				            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
    				        ];
    				        $this->entityPhones->addRow($dataPhone);
    				    }else{
    				        $dataPhone = [];
    				        $dataPhone = [
    				            'agents_id' => $agentId,
    				            'fullname' => ($itemSheet['D'] != '') ? $itemSheet['D'] : $currentPhone['fullname']
    				        ];
    				        $this->entityPhones->updateRow($phone, $dataPhone);
    				    }
				    }
				}
				$n++;
			}
		}
		return true;
	}
    
    public function iframeAction(){
        $view = new ViewModel();
        $this->layout()->setTemplate('empty/layout');
        $id = $this->params()->fromRoute('id', 0);
        $view->setVariable('id', $id);
        $queries = $this->params()->fromQuery();
        // $id = (int)$this->params()->fromRoute('id', 0);
        // $valueCurrent = $this->entityUsers->fetchRow($id);
        // \Zend\Debug\Debug::dump($queries); die();
        // if(empty($valueCurrent)){
        //     die('success');
        // }
        $view->setVariable('queries', $queries);
        return $view;
    }
}
