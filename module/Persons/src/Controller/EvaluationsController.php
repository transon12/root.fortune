<?php

namespace Persons\Controller;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Persons\Form\Evaluations\AddForm;
use Persons\Form\Evaluations\DeleteForm;
use Persons\Form\Evaluations\EditForm;
use Persons\Form\Evaluations\SearchForm;
use Persons\Model\EvaluationCriterias;
use Persons\Model\Evaluations;
use Persons\Model\ManageEvaluations;
use Persons\Model\Profiles;
use Settings\Model\Settings;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class EvaluationsController extends AdminCore{
    public $entitySettings;
    public $entityEvaluationCriterias;
    public $entityEvaluations;
    public $entityProfiles;
    public $entityManageEvaluations;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    EvaluationCriterias $entityEvaluationCriterias, Evaluations $entityEvaluations, Profiles $entityProfiles,
    ManageEvaluations $entityManageEvaluations){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityEvaluationCriterias = $entityEvaluationCriterias;
        $this->entityEvaluations = $entityEvaluations;
        $this->entityProfiles = $entityProfiles;
        $this->entityManageEvaluations = $entityManageEvaluations;
    }

    public function viewAction(){
        $userId = (int)$this->params()->fromRoute('id', 0);
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $optionYears = $this->entityEvaluations->fetchAllYears();
        $formSearch->get('year')->setValueOptions([""=>"---Chọn một năm---"] + $optionYears);
        $formSearch->setData($queries);
        $arrEvaluations = $this->entityEvaluations->fetchAlls($userId,$queries) ;
        
        return new ViewModel([
            'arrEvaluations' => $arrEvaluations,
            'formSearch' => $formSearch,
            'optionName' => $this->entityProfiles->fetchNameByUserId(),
            'userId' => $userId,
        ]);
    }

    public function indexAction(){
        $userId = $this->sessionContainer->id;
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $optionYears = $this->entityEvaluations->fetchAllYears();
        $formSearch->get('year')->setValueOptions([""=>"---Chọn một năm---"] + $optionYears);
        $formSearch->setData($queries);
        $arrEvaluations = $this->entityEvaluations->fetchAlls($userId,$queries) ;
       
        return new ViewModel([
            'arrEvaluations' => $arrEvaluations,
            'formSearch' => $formSearch,
            'optionName' => $this->entityProfiles->fetchNameByUserId(),
            'userId' => $userId,
        ]);
    }

    public function addAction(){
        $userId = $this->sessionContainer->id;
        $dataEvaluation = $this->entityEvaluationCriterias->fetchAllCode();
        $dataPoint = $this->entityEvaluationCriterias->fetchAllPoint();
        // \Zend\Debug\Debug::dump($dataPoint); die();
        $request = $this->getRequest();
        $form = new AddForm($dataEvaluation);
        $valuePost = null;
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            // \Zend\Debug\Debug::dump($valuePost); die();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'user_id' => $userId,
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                    'year' => $valuePost['year'],
                    'personal_comment' => $valuePost['personal_comment'],
                ];
                unset($valuePost['btnSubmit']);
                unset($valuePost['year']);
                unset($valuePost['personal_comment']);
                // \Zend\Debug\Debug::dump($valuePost); die();
                $data['content'] = json_encode($valuePost);
                $this->entityEvaluations->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        return new ViewModel([
            'valuePost' => $valuePost,
            'dataEvaluation' => $dataEvaluation,
            'form' => $form,
            'dataPoint' => $dataPoint,
            'userId' => $userId,
        ]);
    }

    public function editAction(){
        $userId = $this->sessionContainer->id;
        $paramUserId = (int)$this->params()->fromRoute('id', 0);
        $arrManageEvaluations = $this->entityManageEvaluations->fetchAllToOption($paramUserId);
        if($userId != $paramUserId && !in_array($userId, $arrManageEvaluations)){
            // $this->flashMessenger()->addWarningMessage('Không thể đánh giá ');
            die("Không thể đánh giá");
        }
        $id = (int)$this->params()->fromRoute('evaluation_id', 0);
        $dataEvaluation = $this->entityEvaluationCriterias->fetchAllCode();
        $dataPoint = $this->entityEvaluationCriterias->fetchAllPoint();
        $valueCurrent = $this->entityEvaluations->fetchRow($paramUserId, $id);
        if(empty($valueCurrent)){
            die('Không có dữ liệu');
        }
        $valuePost = $valueCurrent;
        $settingDatas = json_decode($valueCurrent['content'],true);
        $status = $valueCurrent['status'];
        $request = $this->getRequest();
        $form = new EditForm($settingDatas);
        if($status == 3 || ($status != 1 && $userId == $paramUserId)){
            $form->get('personal_comment')->setAttributes(['disabled'=>true]);
            $form->get('general_comment')->setAttributes(['disabled'=>true]);
            foreach($settingDatas as $key => $item){
                $form->get($key.'[point]')->setAttributes(['disabled'=>true]);
                $form->get($key.'[expression]')->setAttributes(['disabled'=>true]);
                $form->get($key.'[point_manager]')->setAttributes(['disabled'=>true]);
                $form->get($key.'[expression_manager]')->setAttributes(['disabled'=>true]);
            }
        }
        // if($userId == $paramUserId){
        //     foreach($settingDatas as $key => $item){
        //         $form->get($key.'[point_manager]')->setAttributes(['disabled'=>true]);
        //         $form->get($key.'[expression_manager]')->setAttributes(['disabled'=>true]);
        //     }
        // }
        $form->setData($valuePost);
        if($request->isPost()){
            if($status != 3 && ($status != 2 || $userId != $paramUserId)){
                $valuePost = $request->getPost()->toArray();
                // \Zend\Debug\Debug::dump($valuePost); die();
                $form->setData($valuePost);
                if($form->isValid()){
                    $data = [
                        'year' => $valuePost['year'],
                        'general_comment' => $valuePost['general_comment'],
                        'personal_comment' => $valuePost['personal_comment'],
                    ];
                    if($userId != $paramUserId){
                        $data['status'] = isset($valuePost['btnSubmit']) ? 2 : (isset($valuePost['btnComplete']) ? 3 : 2);
                        // $data['status'] = 2;
                    }
                    unset($valuePost['btnSubmit']);
                    unset($valuePost['btnComplete']);
                    unset($valuePost['year']);
                    unset($valuePost['general_comment']);
                    unset($valuePost['personal_comment']);
                    $data['content'] = json_encode($valuePost);
                    $this->entityEvaluations->updateRow($paramUserId, $id, $data);
                    $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                    return $this->redirect()->toRoute('persons/evaluations', ['action' => 'edit', 'id' => $paramUserId, 'evaluation_id' => $id]);
                }else{
                    $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
                }
            }else{
                $this->flashMessenger()->addWarningMessage('Không thể sửa đánh giá');
            }
        }
        return new ViewModel([
            'dataEvaluation' => $dataEvaluation,
            'form' => $form,
            'dataPoint' => $dataPoint,
            'userId' => $userId,
            'paramUserId' => $paramUserId,
            'status' => $status,
        ]);
    }

    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $userId = $this->sessionContainer->id;
        $id = (int)$this->params()->fromRoute('evaluation_id', 0);
        $requests = $this->getRequest();
        $form = new DeleteForm($requests->getRequestUri());
        $valueCurrent = $this->entityEvaluations->fetchRow($userId, $id);
        // \Zend\Debug\Debug::dump($valueCurrent);die();
        if(empty($valueCurrent)){
            die('Not found!');
        }
        if($requests->isPost()){
            // $this->entityUserKpis->updateRow($id, $userId,['status'=> '-1']);
            $this->entityEvaluations->updateRow($userId, $id, ['status'=> '-1']);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        return new ViewModel([
            'form' => $form
        ]);
    }

    public function reviewerAction(){
        $this->layout()->setTemplate('empty/layout');
        $userId = $this->params()->fromRoute('id', 0);
        $userProfile = $this->entityProfiles->fetchRowAsUserId($userId);
        // $reviewer = $userProfile['user_id_reviewer'];
        return new ViewModel([
            'userId' => $userId,
            'userProfile' => $userProfile
        ]);
    }

    public function reviewersAction(){
        $userId = $this->params()->fromRoute('id', 0);
        $arrManageEvaluations = $this->entityManageEvaluations->fetchAlls($userId);
        header("Content-Type: application/json");
        echo json_encode($arrManageEvaluations);
        die();
    }

    public function profileAction(){
        $userId = $this->params()->fromRoute('id', 0);
        $arrProfile = $this->entityProfiles->fetchAlls(['user_id'=>$userId]);
        //  \Zend\Debug\Debug::dump($arrProfile); die();
        header("Content-Type: application/json");
        echo json_encode($arrProfile);
        die();
    }

    public function addReviewerAction(){
        $this->layout()->setTemplate('empty/layout');
        $userId = $this->params()->fromRoute('id', 0);
        $requests = $this->getRequest();
        if($requests->isPost()){
            $valuePost = $requests->getPost()->toArray();
            if(empty($valuePost) || $valuePost["user_id_reviewer"] == ""){
                die();
            }
            // $arrManageEvaluations = $this->entityManageEvaluations->fetchAllToOption($userId);
            // if(in_array($valuePost["user_id_reviewer"],$arrManageEvaluations)){
            //     $this->flashMessenger()->addWarningMessage('Người này đã được thêm vào trước đó');
            //     return false;
            // }
            // \Zend\Debug\Debug::dump($valuePost); die();
            $data = [
                'user_id' => $userId,
                'user_id_reviewer' => $valuePost['user_id_reviewer'],
            ];
            $this->entityManageEvaluations->addRow($data);
        }else{
            die();
        }
        die();
    }

    public function editReviewerAction(){
        $this->layout()->setTemplate('empty/layout');
        $userId = $this->params()->fromRoute('id', 0);
        $requests = $this->getRequest();
        if($requests->isPost()){
            $valuePost = $requests->getPost()->toArray();
            // \Zend\Debug\Debug::dump($valuePost); die();
            $id = $valuePost["id"];
            $valueCurrent = $this->entityManageEvaluations->fetchRowById($id, $userId);
            
            if(empty($valueCurrent)){
                die();
            }
            if(empty($valuePost) || $valuePost["user_id_reviewer"] == ""){
                die();
            }
            // $arrManageEvaluations = $this->entityManageEvaluations->fetchAllToOption($userId);
            // if(in_array($valuePost["user_id_reviewer"],$arrManageEvaluations)){
            //     $this->flashMessenger()->addWarningMessage('Người này đã được thêm vào trước đó');
            //     return false;
            // }
            $data = [
                'user_id_reviewer' => $valuePost['user_id_reviewer'],
            ];
            $this->entityManageEvaluations->updateRow($userId, $id, $data);
        }else{
            die();
        }
        die();
    }

    public function deleteReviewerAction(){
        $this->layout()->setTemplate('empty/layout');
        $userId = $this->params()->fromRoute('id', 0);
        $requests = $this->getRequest();
        if($requests->isPost()){
            $valuePost = $requests->getPost()->toArray();
            $id = $valuePost["id"];
            $valueCurrent = $this->entityManageEvaluations->fetchRowById($id, $userId);
            if(empty($valueCurrent)){
                die();
            }
            $valueCurrent = $this->entityManageEvaluations->deleteRow($id, $userId);
        }else{
            die();
        }
        die();
    }
}