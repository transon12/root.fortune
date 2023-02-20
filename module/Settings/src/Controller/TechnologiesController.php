<?php

namespace Settings\Controller;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Settings\Form\Technologies\AddForm;
use Settings\Form\Technologies\DeleteForm;
use Settings\Form\Technologies\EditForm;
use Settings\Form\Technologies\SearchForm;
use Settings\Model\Settings;
use Settings\Model\Technologies;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class TechnologiesController extends AdminCore{

    public $entitySettings;
    public $entityTechnologies;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Technologies $entityTechnologies){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityTechnologies = $entityTechnologies;
    }

    public function indexAction(){
       
        $form = new SearchForm();
        $queries = $this->params()->fromQuery();
        $form->setData($queries);

        $arrTechnologies = new Paginator(new ArrayAdapter($this->entityTechnologies->fetchAlls($queries)));
        
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;

        $arrTechnologies->setCurrentPageNumber($page);
        $arrTechnologies->setItemCountPerPage($perPage);
        $arrTechnologies->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrTechnologies' => $arrTechnologies,
            'contentPaginator' => $contentPaginator,
            'form' => $form,
            'queries' => $queries
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
                    'name' => $valuePost['name'],
                    'description' => $valuePost['description'],
                    'status' => $valuePost['status'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entityTechnologies->addRow($data);
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
        $id = $this->params()->fromRoute('id', '');
        $valueCurrent = $this->entityTechnologies->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('settings/technologies');
        }else{
            $valuePost = $valueCurrent;
        }
        $request = $this->getRequest();
        $form = new EditForm($request->getRequestUri());
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'name' => $valuePost['name'],
                    'description' => $valuePost['description'],
                    'status' => $valuePost['status'],
                ];
                $this->entityTechnologies->updateRow($id,$data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $form->setData($valuePost);
        return new ViewModel(['form'=>$form]);
    }

    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = $this->params()->fromRoute('id', '');
        $valueCurrent = $this->entityTechnologies->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('settings/technologies');
        }
        $request = $this->getRequest();
        $form = new DeleteForm($request->getRequestUri());
        if($request->isPost()){
            $this->entityTechnologies->updateRow($id,['status'=>'-1']);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        return new ViewModel([
            'form' => $form,
            'valueCurrent' => $valueCurrent
        ]);
    }
}