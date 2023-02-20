<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Supplies\Controller;

use Zend\View\Model\ViewModel;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Settings\Model\Settings;
use Supplies\Model\Supplies;
use Supplies\Form\Index\AddForm;
use Supplies\Form\Index\SearchForm;
use Supplies\Form\Index\EditForm;
use Supplies\Form\Index\DeleteForm;
use Settings\Model\Companies;

class IndexController extends AdminCore{
    
    public $entitySupplies;
    public $entitySettings;
    public $entityCompanies;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, Supplies $entitySupplies,
        Companies $entityCompanies) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entitySupplies = $entitySupplies;
        $this->entityCompanies = $entityCompanies;
    }
    
    public function indexAction(){
        $formSearch = new SearchForm('index', $this->sessionContainer->id);
        $optionCompanies = $this->entityCompanies->fetchAllToOptions();
        if($this->sessionContainer->id == '1'){
            $formSearch->get('company_id')->setValueOptions( [
                '' => '--- Chọn một công ty ---'] + 
                $optionCompanies 
            );
        }
        $queries = $this->params()->fromQuery();
        // reset company_id if empty
        if(!isset($queries['company_id']) || $queries['company_id'] == ''){
            $queries['company_id'] = isset($queries['company_id']) ? $queries['company_id'] : $this->defineCompanyId;
        }
        $formSearch->setData($queries);
        
        $arrSupplies = new Paginator(new ArrayAdapter( $this->entitySupplies->fetchAlls($queries) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrSupplies->setCurrentPageNumber($page);
        $arrSupplies->setItemCountPerPage($perPage);
        $arrSupplies->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrSupplies' => $arrSupplies, 
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'userId' => $this->sessionContainer->id,
            'optionCompanies' => $optionCompanies
        ]);
    }
    
    public function addAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        
        $request = $this->getRequest();
        $form = new AddForm($request->getRequestUri());
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'company_id' => $this->defineCompanyId,
                    'code' => $valuePost['code'],
                    'name' => $valuePost['name'],
                    'unit' => $valuePost['unit'],
                    'allow_minimum' => $valuePost['allow_minimum'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entitySupplies->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $view->setVariable('form', $form);
        return $view;
    }
    
    public function editAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entitySupplies->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('supplies/index');
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
                    'code' => $valuePost['code'],
                    'name' => $valuePost['name'],
                    'unit' => $valuePost['unit'],
                    'allow_minimum' => $valuePost['allow_minimum']
                ];
                $this->entitySupplies->updateRow($id, $data);
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
        $valueCurrent = $this->entitySupplies->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('supplies/index');
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];
        
        if($request->isPost()){
            $this->entitySupplies->updateRow($id, ['status' => '-1']);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form, 
            'checkRelationship' => $checkRelationship, 
            'valueCurrent' => $valueCurrent
        ]);
    }
}
