<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Admin\Form\Positions\AddForm;
use Admin\Form\Positions\EditForm;
use Admin\Form\Positions\DeleteForm;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Admin\Model\Positions;
use Admin\Model\GroupsPositionsUsers;
use Admin\Form\Positions\SearchForm;
use Settings\Model\Companies;

class PositionsController extends AdminCore{
    public $entityPositions;
    public $entitySettings;
    public $entityGroupsPositionsUsers;
    public $entityCompanies;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, 
        Positions $entityPositions, GroupsPositionsUsers $entityGroupsPositionsUsers, Companies $entityCompanies) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityPositions = $entityPositions;
        $this->entityGroupsPositionsUsers = $entityGroupsPositionsUsers;
        $this->entityCompanies = $entityCompanies;
    }
    
    public function indexAction(){
        $formSearch = new SearchForm('index', $this->sessionContainer->id);
        if($this->sessionContainer->id == '1'){
            $arrCompanies = $this->entityCompanies->fetchAllToOptions();
            $formSearch->get('company_id')->setValueOptions( [
                '' => '--- Chọn một công ty ---'] +
                $arrCompanies
                );
        }
        
        $queries = $this->params()->fromQuery();
        // reset company_id if empty
        if(!isset($queries['company_id']) || $queries['company_id'] == ''){
            $queries['company_id'] = isset($queries['company_id']) ? $queries['company_id'] : $this->defineCompanyId;
        }
        $formSearch->setData($queries);
        
        $arrPositions = new Paginator(new ArrayAdapter( $this->entityPositions->fetchAlls($queries) ));
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrPositions->setCurrentPageNumber($page);
        $arrPositions->setItemCountPerPage($perPage);
        $arrPositions->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrPositions' => $arrPositions, 
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'userId' => $this->sessionContainer->id
        ]);
    }
    
    public function addAction(){
        $view = new ViewModel();
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new AddForm($request->getRequestUri(), $this->sessionContainer->id);
        
        if($this->sessionContainer->id == '1'){
            $arrCompanies = $this->entityCompanies->fetchAllToOptions();
            $form->get('company_id')->setValueOptions( [
                '' => '--- Chọn một công ty ---'] + 
                $arrCompanies 
            );
        }
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if((int)$valuePost['level'] <= 0 && $this->sessionContainer->id != '1'){
                $form->get('level')->setMessages($form->get('level')->getMessages() + ['level_min' => 'Cấp độ phải cấu hình lớn hơn hoặc bằng 1']);
                $isValid = false;
            }
            if($isValid){
                $data = [
                    'name' => $valuePost['name'],
                    'level' => $valuePost['level'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                if($this->sessionContainer->id == '1'){
                    $data['company_id'] = $valuePost['company_id'];
                }else{
                    $data['company_id'] = '';
                }
                $this->entityPositions->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }

        $view->setVariable('form', $form);
        $view->setVariable('userId', $this->sessionContainer->id);
        return $view;
    }
    
    public function editAction(){
        $view = new ViewModel();
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityPositions->fetchRow($id);
        
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('admin/positions');
        }else{
            $valuePost = $valueCurrent; 
        }
        
        $form = new EditForm($request->getRequestUri(), $this->sessionContainer->id);
        
        if($this->sessionContainer->id == '1'){
            $arrCompanies = $this->entityCompanies->fetchAllToOptions();
            $form->get('company_id')->setValueOptions( [
                '' => '--- Chọn một công ty ---'] + 
                $arrCompanies 
            );
            $form->get('company_id')->setAttribute('readonly', 'readonly');
        }
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if((int)$valuePost['level'] <= 0 && $this->sessionContainer->id != '1'){
                $form->get('level')->setMessages($form->get('level')->getMessages() + ['level_min' => 'Cấp độ phải cấu hình lớn hơn hoặc bằng 1']);
                $isValid = false;
            }
            if($isValid){
                $data = [
                    'name' => $valuePost['name'],
                    'level' => $valuePost['level'],
                ];
                $this->entityPositions->updateRow($id, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }else{
            $form->setData($valuePost);
        }

        $view->setVariable('userId', $this->sessionContainer->id);
        $view->setVariable('form', $form);
        return $view;
    }
    
    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityPositions->fetchRow($id);
        if(empty($valueCurrent)){
            die('success');
        }
        $form = new DeleteForm($request->getRequestUri());
        // check relationship
        $checkRelationship = [];
//         $arrGroupsPositionsUsers = $this->entityGroupsPositionsUsers->fetchCount(['position_id' => $id]);
//         if($arrGroupsPositionsUsers){
//             $checkRelationship['groups_positions_users'] = 1;
//         }
        
        if($request->isPost()){
//             $this->entityGroupsPositionsUsers->deleteRows(['positions_id' => $id]);
            if((int)$valueCurrent['level'] >= 1){
                $this->entityPositions->updateRow($id, ['status' => '-1']);
                $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            }else{
                $this->flashMessenger()->addWarningMessage('Bạn không thể xóa chức vụ này, đề nghị liên hệ quản lý để biết thêm thông tin!');
            }
            die('success');
        }
        
        return new ViewModel(['form' => $form, 'checkRelationship' => $checkRelationship, 'valueCurrent' => $valueCurrent]);
    }
    
}
