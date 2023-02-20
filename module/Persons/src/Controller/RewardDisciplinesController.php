<?php

namespace Persons\Controller;

use Admin\Core\AdminCore;
use Admin\Model\Groups;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Persons\Form\RewardDisciplines\AddForm;
use Persons\Form\RewardDisciplines\DeleteForm;
use Persons\Form\RewardDisciplines\EditForm;
use Persons\Form\RewardDisciplines\SearchForm;
use Persons\Model\Profiles;
use Persons\Model\RewardDisciplines;
use Settings\Model\Settings;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class RewardDisciplinesController extends AdminCore{

    public $entitySettings;
    public $entityUsers;
    public $entityProfiles;
    public $entityRewardDisciplines;
    public $entityGroups;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Users $entityUsers, Profiles $entityProfiles, RewardDisciplines $entityRewardDisciplines, Groups $entityGroups){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityUsers = $entityUsers;
        $this->entityProfiles = $entityProfiles;
        $this->entityRewardDisciplines = $entityRewardDisciplines;
        $this->entityGroups = $entityGroups;
    }

    public function indexAction(){
        $queries = $this->params()->fromQuery();
        $formSearch = new SearchForm();
        $formSearch->get("type")->setValueOptions(["" => "--- Chọn một loại ---" ,"1" => "Khen thưởng", "0" => "Kỷ luật"]);
        $formSearch->setData($queries);
        $arrRewardDisciplines = new Paginator(new ArrayAdapter($this->entityRewardDisciplines->fetchAlls($queries)));
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrRewardDisciplines->setCurrentPageNumber($page);
        $arrRewardDisciplines->setItemCountPerPage($perPage);
        $arrRewardDisciplines->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'formSearch' => $formSearch,
            'arrRewardDisciplines' => $arrRewardDisciplines,
            'contentPaginator' => $contentPaginator,
            'optionGroup' => $this->entityGroups->fetchOptionByName(),
            'optionProfiles' => $this->entityProfiles->fetchAllByName(),
        ]);
    }

    public function addAction(){
        $request = $this->getRequest();
        $form = new AddForm();
        $optionGroups = $this->entityGroups->fetchOptionByName();
        $form->get('group_id')->setValueOptions(["" => "--- Chọn phòng ban ---"] + $optionGroups);
        $optionProfiles = $this->entityProfiles->fetchAllByName();
        $form->get('profile_id')->setValueOptions(["" => "--- Chọn nhân viên ---"] + $optionProfiles);

        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $valuePost['date'] = (isset($valuePost['date'])) ? $valuePost['date'] : null;
                $date = date_create_from_format('d/m/Y H:i:s', $valuePost['date']);
                $data = [
                    'profile_id' => $valuePost['profile_id'],
                    'group_id' => $valuePost['group_id'],
                    'date' => date_format($date, 'Y-m-d H:i:s'),
                    'content' => $valuePost['content'],
                    'proposal' => $valuePost['proposal'],
                    'type' => $valuePost['type'],
                    'level' => $valuePost['level'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                    'status' => 1,
                ];
                $this->entityRewardDisciplines->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
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
        $id = $this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityRewardDisciplines->fetchRow($id);
        if(empty($valueCurrent)){
            die('Not found!');
        }else{
            $valuePost = $valueCurrent;
            $valuePost['date'] = (isset($valuePost['date'])) ? date('d/m/Y H:i:s',strtotime($valuePost['date'])) : '';
        }
        $request = $this->getRequest();
        $form = new EditForm($request->getRequestUri());

        $optionGroups = $this->entityGroups->fetchOptionByName();
        $form->get('group_id')->setValueOptions(["" => "--- Chọn phòng ban ---"] + $optionGroups);
        $optionProfiles = $this->entityProfiles->fetchAllByName();
        $form->get('profile_id')->setValueOptions(["" => "--- Chọn nhân viên ---"] + $optionProfiles);

        $form->setData($valuePost);

        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $valuePost['date'] = (isset($valuePost['date'])) ? $valuePost['date'] : null;
                $date = date_create_from_format('d/m/Y H:i:s', $valuePost['date']);
                $data = [
                    'profile_id' => $valuePost['profile_id'],
                    'group_id' => $valuePost['group_id'],
                    'date' => date_format($date, 'Y-m-d H:i:s'),
                    'content' => $valuePost['content'],
                    'proposal' => $valuePost['proposal'],
                    'type' => $valuePost['type'],
                    'level' => $valuePost['level'],
                ];
                $this->entityRewardDisciplines->updateRow($data, $id);
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
        $id = $this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityRewardDisciplines->fetchRow($id);
        if(empty($valueCurrent)){
            die('Not found!');
        }
        $request = $this->getRequest();
        $form = new DeleteForm($request->getRequestUri());
        if($request->isPost()){
            $this->entityRewardDisciplines->updateRow(['status'=> '-1'],$id);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        return new ViewModel([
            'form' => $form,
            'valueCurrent' => $valueCurrent
        ]);
    }
}