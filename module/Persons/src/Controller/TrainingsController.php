<?php

namespace Persons\Controller;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Persons\Form\Trainings\AddForm;
use Persons\Form\Trainings\DeleteForm;
use Persons\Form\Trainings\EditForm;
use Persons\Form\Trainings\SearchForm;
use Persons\Model\Profiles;
use Persons\Model\Trainings;
use Settings\Model\Settings;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class TrainingsController extends AdminCore{

    public $entitySettings;
    public $entityUsers;
    public $entityProfiles;
    public $entityTrainings;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Users $entityUsers, Profiles $entityProfiles, Trainings $entityTrainings){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityUsers = $entityUsers;
        $this->entityProfiles = $entityProfiles;
        $this->entityTrainings = $entityTrainings;
    }

    public function indexAction(){
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        $optionProfile = $this->entityProfiles->fetchAllByName();
        $arrTrainings = new Paginator(new ArrayAdapter($this->entityTrainings->fetchAlls($queries)));
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrTrainings->setCurrentPageNumber($page);
        $arrTrainings->setItemCountPerPage($perPage);
        $arrTrainings->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'formSearch' => $formSearch,
            'arrTrainings' => $arrTrainings,
            'contentPaginator' => $contentPaginator,
            'optionProfile' => $optionProfile,
        ]);
    }

    public function addAction(){
        $request = $this->getRequest();
        $form = new AddForm();
        $optionProfile = $this->entityProfiles->fetchAllByName();
        $form->get('participants')->setValueOptions($optionProfile);
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $valuePost['date'] = (isset($valuePost['date'])) ? $valuePost['date'] : null;
                $date = date_create_from_format('d/m/Y H:i:s', $valuePost['date']);
                $data = [
                    'name' => $valuePost['name'],
                    'date' => date_format($date, 'Y-m-d H:i:s'),
                    'location' => $valuePost['location'],
                    'trainer' => $valuePost['trainer'],
                    'participants' => implode(", ", $valuePost['participants']),
                    'content' => $valuePost['content'],
                    'document' => $valuePost['document'],
                    'other_info' => $valuePost['other_info'],
                    'fee' => $valuePost['fee'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                    'status' => 1,
                ];
                $this->entityTrainings->addRow($data);
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
        $valueCurrent = $this->entityTrainings->fetchRow($id);
        if(empty($valueCurrent)){
            die('Not found!');
        }else{
            $valuePost = $valueCurrent;
            $valuePost['date'] = (isset($valuePost['date'])) ? date('d/m/Y H:i:s',strtotime($valuePost['date'])) : '';
            $valuePost['participants'] = explode(", ",$valuePost['participants'] );
        }
        
        $request = $this->getRequest();
        $form = new EditForm($request->getRequestUri());

        $optionProfile = $this->entityProfiles->fetchAllByName();
        $form->get('participants')->setValueOptions($optionProfile);
        $form->setData($valuePost);

        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $valuePost['date'] = (isset($valuePost['date'])) ? $valuePost['date'] : null;
                $date = date_create_from_format('d/m/Y H:i:s', $valuePost['date']);
                $data = [
                    'name' => $valuePost['name'],
                    'date' => date_format($date, 'Y-m-d H:i:s'),
                    'location' => $valuePost['location'],
                    'trainer' => $valuePost['trainer'],
                    'participants' => implode(", ", $valuePost['participants']),
                    'content' => $valuePost['content'],
                    'document' => $valuePost['document'],
                    'other_info' => $valuePost['other_info'],
                    'fee' => $valuePost['fee'],
                ];
                $this->entityTrainings->updateRow($data, $id);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        return new ViewModel([
            'form' => $form,
            'valueCurrent' => $valueCurrent,
        ]);
    }

    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = $this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityTrainings->fetchRow($id);
        if(empty($valueCurrent)){
            die('Not found!');
        }
        $request = $this->getRequest();
        $form = new DeleteForm($request->getRequestUri());
        if($request->isPost()){
            $this->entityTrainings->updateRow(['status'=> '-1'],$id);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        return new ViewModel([
            'form' => $form,
            'valueCurrent' => $valueCurrent
        ]);
    }
}