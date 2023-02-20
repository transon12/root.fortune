<?php

namespace Storehouses\Controller;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Codes\Model\Codes;
use Settings\Model\Companies;
use Settings\Model\Settings;
use Storehouses\Form\Bills\AddForm;
use Storehouses\Form\Bills\AddProductForm;
use Storehouses\Form\Bills\DeleteForm;
use Storehouses\Form\Bills\EditForm;
use Storehouses\Form\Bills\SearchForm;
use Storehouses\Model\Agents;
use Storehouses\Model\BillDetails;
use Storehouses\Model\Bills;
use Storehouses\Model\Products;
use Storehouses\Model\Storehouses;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class BillsController extends AdminCore{

    public $entityStorehouses;
    public $entitySettings;
    public $entityCountries;
    public $entityCities;
    public $entityDistricts;
    public $entityWards;
    public $entityCompanies;
    public $entityUsers;
    public $entityProducts;
    public $entityAgents;
    public $entityBills;
    public $entityBillDetails;
    public $entityCodes;
    

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Storehouses $entityStorehouses,Products $entityProducts , Agents $entityAgents ,
    Bills $entityBills,Users $entityUsers, BillDetails $entityBillDetails, Codes $entityCodes){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityStorehouses = $entityStorehouses;
        $this->entityUsers = $entityUsers;
        $this->entityProducts = $entityProducts;
        $this->entityAgents = $entityAgents;
        $this->entityBills = $entityBills;
        $this->entityBillDetails = $entityBillDetails;
        $this->entityCodes = $entityCodes;
    }

    public function indexAction(){
        $agentId = (int)$this->params()->fromRoute('agentid', 0);
        
        $valueCurrent = $this->entityAgents->fetchAll($agentId,["company_id" => $this->defineCompanyId]);
        //\Zend\Debug\Debug::dump($valueCurrent); die();
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('storehouses/agents');
        }
        // $a=$this->getEvent()->getRouteMatch()->getParam('id');
        // echo $a; die();
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        $queries['user_id'] = $this->sessionContainer->id;

        $arrBills = new Paginator(new ArrayAdapter( $this->entityBills->fetchAll($agentId,$queries) ));
        $optionAgents = $this->entityAgents->fetchAllToOption($this->sessionContainer->id);
        $optionUsers = $this->entityUsers->fetchAllOptions(["company_id" => $this->defineCompanyId]);

        $page = (int) $this->params()->fromQuery('page',1);
        $page = ($page < 1) ?  1 : $page;
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;

        $arrBills->setCurrentPageNumber($page);
        $arrBills->setItemCountPerPage($perPage);
        $arrBills->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'optionAgents' => $optionAgents,
            'contentPaginator' => $contentPaginator,
            'optionUsers'=> $optionUsers,
            'arrBills' => $arrBills,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'agentId' => $agentId
        ]);
    }

    public function addAction(){
        if($this->defineCompanyId == null){
            $this->flashMessenger()->addWarningMessage('Tài khoản này không có quyền tại đây!');
            die('success');
        }
        $view = new ViewModel();
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        $form = new AddForm($request->getRequestUri(),$this->sessionContainer->id);
        //$agentId=$this->getEvent()->getRouteMatch()->getParam('agentid');
        $agentId = (int)$this->params()->fromRoute('agentid', 0);
        $valueCurrent = $this->entityAgents->fetchAll($agentId,["company_id" => $this->defineCompanyId]);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('storehouses/agents');
        }
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $exportedAt = date_create_from_format('d/m/Y H:i:s', date("d/m/Y H:i:s", time()));
                $data = [
                    'agent_id' => $agentId,
                    'company_id' => COMPANY_ID,
                    'user_id' => $this->sessionContainer->id,
                    'name' => $valuePost['name'],
                    'exported_at' => date_format($exportedAt, 'Y-m-d H:i:s')
                ];
                $this->entityBills->addRow($data);
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
    
    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        //echo $id; die();
        $valueCurrent = $this->entityBills->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('agents/index');
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];

        if($request->isPost()){
            $this->entityBills->deleteRow($id);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        return new ViewModel([
            'form' => $form, 
            'checkRelationship' => $checkRelationship, 
            'valueCurrent' => $valueCurrent
        ]);
    }

    public function editAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityBills->fetchRow($id);
        //\Zend\Debug\Debug::dump($valueCurrent); die();
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('bills/index');
        }else{
            $valuePost = $valueCurrent; 
        }
        $form = new EditForm ($request->getRequestUri());
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'name' => $valuePost['name'],
                ];
                $this->entityBills->updateRow($id, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }else{
           
        }
        $form->setData($valuePost);
        //die('abcdef');
        return new ViewModel(['form' => $form]);
    }

    public function iframeAddAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $billId = (int)$this->params()->fromRoute('id', 0);
        
        $agentId = (int)$this->params()->fromRoute('agentid', 0);
        //echo $billId; die();
        $valueCurrentb = $this->entityBills->fetchRow($billId);
        $valueCurrenta = $this->entityAgents->fetchRow($agentId);
        if(empty($valueCurrenta) && empty($valueCurrentb)){
            die('success');
        }
        $view->setVariable('billId', $billId);
        $view->setVariable('agentId', $agentId);
        return $view;
    }

    public function iframeIndexAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $billId = (int)$this->params()->fromRoute('id', 0);
        
        $agentId = (int)$this->params()->fromRoute('agentid', 0);
        //echo $billId; die();
        $valueCurrentb = $this->entityBills->fetchRow($billId);
        $valueCurrenta = $this->entityAgents->fetchRow($agentId);
        if(empty($valueCurrenta) && empty($valueCurrentb)){
            die('success');
        }
        $view->setVariable('billId', $billId);
        $view->setVariable('agentId', $agentId);
        return $view;
    }
}