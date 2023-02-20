<?php

namespace Persons\Controller;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Persons\Form\Kpis\AddForm;
use Persons\Form\Kpis\DeleteForm;
use Persons\Form\Kpis\EditForm;
use Persons\Form\Kpis\SearchForm;
use Persons\Model\Profiles;
use Persons\Model\UserKpis;
use Settings\Model\Settings;
use Zend\View\Model\ViewModel;

class KpisController extends AdminCore{
    
    public $entitySettings;
    public $entityUserKpis;
    public $entityUsers;
    public $entityProfiles;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    UserKpis $entityUserKpis, Users $entityUsers, Profiles $entityProfiles){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityUserKpis = $entityUserKpis;
        $this->entityUsers = $entityUsers;
        $this->entityProfiles = $entityProfiles;
    }

    public function indexAction(){
        $userId = $this->sessionContainer->id;
        $queries = $this->params()->fromQuery();
        $formSearch = new SearchForm();
        $optionYears = $this->entityUserKpis->fetchAllYears();
        $formSearch->get('year')->setValueOptions([""=>"---Chọn một năm---"] + $optionYears);
        $formSearch->setData($queries);
        $arrUserKpis = $this->entityUserKpis->fetchAlls($userId, $queries);
        return new ViewModel([
            'formSearch' => $formSearch,
            'arrUserKpis' => $arrUserKpis,
            'userId' => $userId,
            'optionName' => $this->entityProfiles->fetchNameByUserId()
        ]);
    }

    public function viewAction(){
        $userId = $this->params()->fromRoute('user_id', 0);
        $queries = $this->params()->fromQuery();
        $formSearch = new SearchForm();
        $optionYears = $this->entityUserKpis->fetchAllYears();
        $formSearch->get('year')->setValueOptions([""=>"---Chọn một năm---"] + $optionYears);
        $formSearch->setData($queries);
        $arrUserKpis = $this->entityUserKpis->fetchAlls($userId, $queries);
        return new ViewModel([
            'formSearch' => $formSearch,
            'arrUserKpis' => $arrUserKpis,
            'userId' => $userId,
            'optionName' => $this->entityProfiles->fetchNameByUserId()
        ]);
    }

    

    public function addAction(){
        $userId = $this->sessionContainer->id;
        $requests = $this->getRequest();
        $form = new AddForm();
        // $optionUsers = $this->entityUsers->fetchUserAsCompanies();
        // $form->get('user_id')->setValueOptions(['' => '---Chọn một nhân viên---'] + $optionUsers);
        if($requests->isPost()){
            $valuePost = $requests->getPost()->toArray();
            $form->setData($valuePost);
            // \Zend\Debug\Debug::dump($valuePost);die();
            if($form->isValid()){
                foreach($valuePost['data_repeater'] as $item){
                    $data = [
                        'user_id' => $userId,
                        'target' => $item['target'],
                        'measure' => str_replace("\n", "<br>",$item['measure']),
                        'expected_results' => str_replace("\n", "<br>",$item['expected_results']),
                        'action_program' => str_replace("\n", "<br>",$item['action_program']),
                        'results' => str_replace("\n", "<br>",$item['results']),
                        'year' => $valuePost['year'],
                    ];
                    $this->entityUserKpis->addRow($data);
                }
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                return $this->redirect()->toRoute('persons/kpis', ['action' => 'index', 'user_id' => $userId]);
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
        $userId = $this->params()->fromRoute('user_id', 0);
        $id = $this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityUserKpis->fetchRowById($id, $userId);
        // \Zend\Debug\Debug::dump($valueCurrent);die();
        if(empty($valueCurrent)){
            die('Not found!');
        }
        $valuePost = $valueCurrent;
        $valuePost['expected_results'] = str_replace("<br>", "",$valuePost['expected_results']);
        $valuePost['measure'] = str_replace("<br>", "",$valuePost['measure']);
        $valuePost['action_program'] = str_replace("<br>", "",$valuePost['action_program']);
        $valuePost['results'] = str_replace("<br>", "",$valuePost['results']);
        $requests = $this->getRequest();
        $form = new EditForm($requests->getRequestUri());
        $form->setData($valuePost);
        if($requests->isPost()){
            $valuePost = $requests->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'user_id' => $userId,
                    'target' => $valuePost['target'],
                    'measure' => str_replace("\n", "<br>",$valuePost['measure']),
                    'expected_results' => str_replace("\n", "<br>",$valuePost['expected_results']),
                    'action_program' => str_replace("\n", "<br>",$valuePost['action_program']),
                    'results' => str_replace("\n", "<br>",$valuePost['results']),
                    'year' => $valuePost['year'],
                ];
                // \Zend\Debug\Debug::dump($data);die();
                $this->entityUserKpis->updateRow($id, $userId, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }else{
            if($valuePost['year'] != ''){
                $valuePost['year'] = date_format(date_create($valuePost['year']), 'YYYY');
            }
        }
        
        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $userId = $this->params()->fromRoute('user_id', 0);
        $id = $this->params()->fromRoute('id', 0);
        $requests = $this->getRequest();
        $form = new DeleteForm($requests->getRequestUri());
        $valueCurrent = $this->entityUserKpis->fetchRowById($id, $userId);
        // \Zend\Debug\Debug::dump($valueCurrent);die();
        if(empty($valueCurrent)){
            die('Not found!');
        }
        if($requests->isPost()){
            $this->entityUserKpis->updateRow($id, $userId,['status'=> '-1']);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        return new ViewModel([
            'form' => $form
        ]);
    }

    // public function viewsAction(){
    //     $userId = $this->params()->fromRoute('user_id', 0);
    //     $arrUserKpis = $this->entityUserKpis->fetchAlls($userId);
    //     header("Content-Type: application/json");
    //     echo(json_encode($arrUserKpis));
    //     die();
    //     // return new ViewModel();
    // }

    // public function addsAction(){
    //     $userId = $this->params()->fromRoute('user_id', 0);
    //     $requests = $this->getRequest();
    //     if($requests->isPost()){
    //         $valuePost = $requests->getPost()->toArray();
    //         $data = [
    //             'user_id' => $userId,
    //             'target' => $valuePost['target'],
    //             'measure' => str_replace("\n", "<br>",$valuePost['measure']),
    //             'expected_results' => str_replace("\n", "<br>",$valuePost['expected_results']),
    //             'action_program' => str_replace("\n", "<br>",$valuePost['action_program']),
    //             'year' => '2021',
    //         ];
    //         $this->entityUserKpis->addRow($data);
    //     }else{
    //         die();
    //     }
    //     die();
    // }

    // public function editsAction(){
    //     $userId = $this->params()->fromRoute('user_id', 0);
    //     $requests = $this->getRequest();
    //     if($requests->isPost()){
    //         $valuePost = $requests->getPost()->toArray();
    //         $id = $valuePost["id"];
    //         $valueCurrent = $this->entityUserKpis->fetchRowById($id, $userId);
    //         if(empty($valueCurrent)){
    //             die();
    //         }
    //         $data = [
    //             'user_id' => $userId,
    //             'target' => $valuePost['target'],
    //             'measure' => str_replace("\n", "<br>",$valuePost['measure']),
    //             'expected_results' => str_replace("\n", "<br>",$valuePost['expected_results']),
    //             'action_program' => str_replace("\n", "<br>",$valuePost['action_program']),
    //             'year' => '2021',
    //         ];
    //         $this->entityUserKpis->updateRow($id, $userId, $data);
    //     }else{
    //         die();
    //     }
    //     die();
    // }

    // public function deletesAction(){
    //     $userId = $this->params()->fromRoute('user_id', 0);
    //     $requests = $this->getRequest();
    //     if($requests->isPost()){
    //         $valuePost = $requests->getPost()->toArray();
    //         $id = $valuePost["id"];
    //         $valueCurrent = $this->entityUserKpis->fetchRowById($id, $userId);
    //         if(empty($valueCurrent)){
    //             die();
    //         }
    //         $this->entityUserKpis->updateRow($id, $userId,['status'=> '-1']);
    //     }else{
    //         die();
    //     }
    //     die();
    // }
}