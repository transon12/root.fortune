<?php

namespace Companies\Controller;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Companies\Form\Addresses\AddForm;
use Companies\Form\Addresses\DeleteForm;
use Companies\Form\Addresses\EditForm;
use Companies\Form\Addresses\SearchForm;
use Companies\Model\Addresses;
use Settings\Model\Settings;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class AddressesController extends AdminCore{
    public $entityCompanies;
    public $entitySettings;
    public $entityAddresses;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Addresses $entityAddresses){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityAddresses = $entityAddresses;
    }

    public function indexAction(){
        $this->layout()->setTemplate('iframe/layout');
        $companyId = $this->params()->fromRoute('id');
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        
        $arrAddresses = new Paginator(new ArrayAdapter($this->entityAddresses->fetchAll($queries,$companyId)));
        //\Zend\Debug\Debug::dump($arrAddresses); die();
        //set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;

        $arrAddresses->setCurrentPageNumber($page);
        $arrAddresses->setItemCountPerPage($perPage);
        $arrAddresses->setPageRange($contentPaginator['page_range']);

        $formSearch->setData($queries);
        return new ViewModel([
            'formSearch' => $formSearch,
            'companyId' => $companyId,
            'arrAddresses' => $arrAddresses,
            'contentPaginator' => $contentPaginator,
        ]);
    }

    public function addAction(){
        $this->layout()->setTemplate('iframe/layout');
        $companyId = $this->params()->fromRoute('id');
        //echo $companyId; die();
        $request = $this->getRequest();
        $form = new AddForm($request->getRequestUri());
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'company_id' => $companyId,
                    'is_type' => $valuePost['is_type'],
                    'address' => $valuePost['address'],
                    'phone' => $valuePost['phone'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                    'status' => $valuePost['status']
                ];
                $this->entityAddresses->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                //$this->redirect()->toRoute('companies/addresses', ['action' => 'index', 'id' => $companyId]);
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        return new ViewModel([
            'form' => $form,
            'companyId' => $companyId,
        ]);
    }

    public function editAction(){
        $this->layout()->setTemplate('iframe/layout');
        $id = (int)$this->params()->fromRoute('addressid',0);
        $companyId = $this->params()->fromRoute('id');
        $valueCurrent = $this->entityAddresses->fetchRow($id,$companyId);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('companies/addresses',['action' => 'index', 'id' => $companyId]);
        }else{
            $valuePost = $valueCurrent;
        }
        $request = $this->getRequest();
        $form = new EditForm($request->getRequestUri());
        $form->setData($valuePost);
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'is_type' => $valuePost['is_type'],
                    'address' => $valuePost['address'],
                    'phone' => $valuePost['phone'],
                    'status' => $valuePost['status']
                ];
                $this->entityAddresses->updateRow($data,$id,$companyId);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        return new ViewModel([
            'companyId' =>$companyId,
            'form' => $form
        ]);
    }

    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $companyId = $this->params()->fromRoute('id', '');
        $id = (int)$this->params()->fromRoute('addressid', 0);
        $valueCurrent = $this->entityAddresses->fetchRow($id,$companyId);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('companies/addresses',['action' => 'index', 'id' => $companyId]);
        }
        $request = $this->getRequest();
        $form = new DeleteForm($request->getRequestUri());
        if($request->isPost()){
            $this->entityAddresses->updateRow(['status'=>'-1'],$id,$companyId);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        return new ViewModel([
            'form' => $form,
            'companyId'=>$companyId,
            'valueCurrent' => $valueCurrent
        ]);
    }
}