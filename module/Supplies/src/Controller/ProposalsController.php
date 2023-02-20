<?php
namespace Supplies\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Supplies\Model\Proposals;
use Supplies\Form\Proposals\SearchForm;
use Supplies\Form\Proposals\AddForm;
use Supplies\Form\Proposals\EditForm;
use Supplies\Form\Proposals\DeleteForm;
use Zend\Session\Container;

class ProposalsController extends AdminCore{
	
    public $entitySettings;
    public $entityProposals;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, Proposals $entityProposals) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityProposals = $entityProposals;
    }
    
    public function indexAction(){
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $queries['company_id'] = $this->defineCompanyId;
        $formSearch->setData($queries);
        
        $arrProposals = new Paginator(new ArrayAdapter( $this->entityProposals->fetchAlls($queries + array('user_id' => $this->sessionContainer->id, 
            'permission' => \Pxt\Permission\Check::checkExportSupplies($this->sessionContainer->permissions))) ));
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrProposals->setCurrentPageNumber($page);
        $arrProposals->setItemCountPerPage($perPage);
        $arrProposals->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrProposals' => $arrProposals, 
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'userIdLogged' => $this->sessionContainer->id,
        ]);
    }
    
    public function addAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new AddForm($request->getRequestUri());
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                
                $data = [
                    'company_id' => $this->defineCompanyId,
                    'user_id' => $this->sessionContainer->id,
                    'name' => $valuePost['name'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entityProposals->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        
        return new ViewModel(['form' => $form]);
    }
    
    public function editAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityProposals->fetchRow($id);
        
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('supplies/proposals');
        }elseif($valueCurrent['user_id'] != $this->sessionContainer->id){
            $this->flashMessenger()->addWarningMessage('Lỗi dữ liệu, đề nghị liên hệ quản lý!');
            $this->redirect()->toRoute('supplies/proposals');
        }else{
            $valuePost = $valueCurrent; 
        }
        
        $form = new EditForm($request->getRequestUri());
        $form->setData($valuePost);
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'name' => $valuePost['name'],
                ];
                $this->entityProposals->updateRow($id, $data);
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
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityProposals->fetchRow($id);
        
        if(empty($valueCurrent)){
            die('success');
        }elseif($valueCurrent['users_id'] != $this->sessionContainer->id){
            $this->flashMessenger()->addWarningMessage('Lỗi dữ liệu, đề nghị liên hệ quản lý!');
            die('success');
        }
        $form = new DeleteForm($request->getRequestUri());
        // check relationship
        $checkRelationship = [];
        if($request->isPost()){
            $this->entityProposals->deleteRow($id);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel([
			'form' => $form, 
			'checkRelationship' => $checkRelationship, 
			'valueCurrent' => $valueCurrent
	    ]);
    }
    
    public function iframeAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityProposals->fetchRow($id);
        //\Zend\Debug\Debug::dump($valueCurrent); die();
        if(empty($valueCurrent)){
            die('success');
        }
        $view->setVariable('id', $id);
        return $view;
    }
    
}
