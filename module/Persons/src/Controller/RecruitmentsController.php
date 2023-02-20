<?php
namespace Persons\Controller;

use Admin\Core\AdminCore;
use Admin\Model\Groups;
use Admin\Model\PxtAuthentication;
use Persons\Form\Recruitments\AddForm;
use Persons\Form\Recruitments\DeleteForm;
use Persons\Form\Recruitments\EditForm;
use Persons\Form\Recruitments\SearchForm;
use Persons\Model\Recruitments;
use Settings\Model\Settings;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class RecruitmentsController extends AdminCore{
    public $entitySettings;
    public $entityRecruitments;
    public $entityGroups;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Recruitments $entityRecruitments, Groups $entityGroups){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityRecruitments = $entityRecruitments;
        $this->entityGroups = $entityGroups;
    }

    public function indexAction(){
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);

        $arrRecruitments = new Paginator(new ArrayAdapter($this->entityRecruitments->fetchAlls($queries)));
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrRecruitments->setCurrentPageNumber($page);
        $arrRecruitments->setItemCountPerPage($perPage);
        $arrRecruitments->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'formSearch' => $formSearch,
            'arrRecruitments' => $arrRecruitments,
            'contentPaginator' => $contentPaginator,
            'queries' => $queries,
            'optionGroup' => $this->entityGroups->fetchOptionByName(),
        ]);
    }

    public function addAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        $form = new AddForm($request->getRequestUri());

        $optionGroup = $this->entityGroups->fetchOptionByName();
        $form->get('group_id')->setValueOptions(["" => "--- Chọn phòng ban ---"] + $optionGroup);

        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            // \Zend\Debug\Debug::dump($valuePost); 
            if($form->isValid()){
                $dateRecruitment = date_create_from_format('d/m/Y H:i:s', $valuePost['date_recruitment']);
                $data = [
                    'position' => $valuePost['position'],
                    'group_id' => $valuePost['group_id'],
                    'amount' => $valuePost['amount'],
                    'date_recruitment' => date_format($dateRecruitment, 'Y-m-d H:i:s'),
                    'description' => $valuePost['description'],
                    'require' => $valuePost['require'],
                    'expected_salary' => $valuePost['expected_salary'],
                    'status' => 1,
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                ];
                
                $this->entityRecruitments->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function editAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = (int)$this->params()->fromRoute('id',0);
        $valueCurrent = $this->entityRecruitments->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('persons/recruitments');
            die('success');
        }else{
            $valuePost = $valueCurrent;
        }
        $request = $this->getRequest();
        $form = new EditForm($request->getRequestUri());

        $optionGroup = $this->entityGroups->fetchOptionByName();
        $form->get('group_id')->setValueOptions(["" => "--- Chọn phòng ban ---"] + $optionGroup);
        $valuePost['date_recruitment'] = date_format(date_create($valuePost['date_recruitment']), 'd/m/Y H:i:s');
        $form->setData($valuePost);
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $dateRecruitment = date_create_from_format('d/m/Y H:i:s', $valuePost['date_recruitment']);
                $data = [
                    'position' => $valuePost['position'],
                    'group_id' => $valuePost['group_id'],
                    'amount' => $valuePost['amount'],
                    'description' => $valuePost['description'],
                    'require' => $valuePost['require'],
                    'date_recruitment' => date_format($dateRecruitment, 'Y-m-d H:i:s'),
                    'expected_salary' => $valuePost['expected_salary'],
                ];
                $this->entityRecruitments->updateRow($id, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        
        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = (int)$this->params()->fromRoute('id',0);
        $valueCurrent = $this->entityRecruitments->fetchRow($id);
        if(empty($valueCurrent)){
            die('success');
        }
        $request = $this->getRequest();
        $form = new DeleteForm($request->getRequestUri());
        if($request->isPost()){
            $this->entityRecruitments->updateRow($id, ['status' => -1]);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        return new ViewModel([
            'form' => $form,
            'id' => $id,
            'valueCurrent' => $valueCurrent,
        ]);
    }
}