<?php

namespace Companies\Controller;

use Admin\Core\AdminCore;
use Admin\Model\Positions;
use Admin\Model\PxtAuthentication;
use Companies\Form\Surrogates\AddForm;
use Companies\Form\Surrogates\DeleteForm;
use Companies\Form\Surrogates\EditForm;
use Companies\Form\Surrogates\SearchForm;
use Companies\Model\Surrogates;
use Settings\Model\Settings;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class SurrogatesController extends AdminCore{
    public $entitySettings;
    public $entitySurrogates;
    public $entityPositions;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Surrogates $entitySurrogates, Positions $entityPositions){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entitySurrogates = $entitySurrogates;
        $this->entityPositions = $entityPositions;
    }

    public function indexAction(){
        $this->layout()->setTemplate('iframe/layout');
        $companyId = $this->params()->fromRoute('id');
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);

        $arrSurrogates = new Paginator(new ArrayAdapter($this->entitySurrogates->fetchAll($queries,$companyId)));
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrSurrogates->setCurrentPageNumber($page);
        $arrSurrogates->setItemCountPerPage($perPage);
        $arrSurrogates->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'formSearch' => $formSearch,
            'arrSurrogates' => $arrSurrogates,
            'contentPaginator' => $contentPaginator,
            'queries' => $queries,
            'companyId' => $companyId,
            'optionPositions' => $this->entityPositions->fetchAllOptions()
        ]);
    }

    public function addAction(){
        $this->layout()->setTemplate('iframe/layout');
        $companyId = $this->params()->fromRoute('id');

        $request = $this->getRequest();
        $form = new AddForm($request->getRequestUri());
        $optionPositions = $this->entityPositions->fetchAllOptions();
        $form->get('positions_id')->setValueOptions([''=> '--- Ch???n m???t ch???c v??? ---'] + $optionPositions);
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'company_id' => $companyId,
                    'positions_id' => $valuePost['positions_id'],
                    'name' => $valuePost['name'],
                    'phone' => $valuePost['phone'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                    'status' => $valuePost['status'],
                ];
                $this->entitySurrogates->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Th??m d??? li???u th??nh c??ng!');
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }

        return new ViewModel([
            'form' => $form,
            'companyId' => $companyId
        ]);
    }

    public function editAction(){
        $this->layout()->setTemplate('iframe/layout');
        $companyId = $this->params()->fromRoute('id');
        $id = $this->params()->fromRoute('surrogateid');

        $valueCurrent = $this->entitySurrogates->fetchRow($id,$companyId);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('companies/surrogates',['action' => 'index', 'id' => $companyId]);
        }else{
            $valuePost = $valueCurrent;
        }

        $request = $this->getRequest();
        $form = new EditForm($request->getRequestUri());
        $optionPositions = $this->entityPositions->fetchAllOptions();
        $form->get('positions_id')->setValueOptions([''=> '--- Ch???n m???t ch???c v??? ---'] + $optionPositions);
        
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'positions_id' => $valuePost['positions_id'],
                    'name' => $valuePost['name'],
                    'phone' => $valuePost['phone'],
                    'status' => $valuePost['status'],
                ];
                $this->entitySurrogates->updateRow($data,$id,$companyId);
                $this->flashMessenger()->addSuccessMessage('S???a d??? li???u th??nh c??ng!');
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }
        $form->setData($valuePost);
        return new ViewModel([
            'form' => $form,
            'companyId' => $companyId,
        ]);
    }

    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $companyId = $this->params()->fromRoute('id', '');
        //echo $companyId; die();
        $id = (int)$this->params()->fromRoute('surrogateid', 0);
        $valueCurrent = $this->entitySurrogates->fetchRow($id,$companyId);
        //\Zend\Debug\Debug::dump($valueCurrent); die();
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('companies/surrogates',['action' => 'index', 'id' => $companyId]);
        }
        $request = $this->getRequest();
        $form = new DeleteForm($request->getRequestUri());
        if($request->isPost()){
            $this->entitySurrogates->updateRow(['status'=> '-1'],$id,$companyId);
            $this->flashMessenger()->addSuccessMessage('X??a d??? li???u th??nh c??ng!');
            die('success');
        }
        return new ViewModel([
            'form' => $form,
            'companyId'=>$companyId,
            'valueCurrent' => $valueCurrent
        ]);
    }
}