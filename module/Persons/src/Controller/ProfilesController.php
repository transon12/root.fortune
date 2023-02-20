<?php

namespace Persons\Controller;

use Admin\Core\AdminCore;
use Admin\Model\Positions;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Persons\Form\Profiles\AddFieldForm;
use Persons\Form\Profiles\DeleteFieldForm;
use Persons\Form\Profiles\EditFieldForm;
use Persons\Form\Profiles\EditForm;
use Persons\Form\Profiles\SearchForm;
use Persons\Model\LeaveLists;
use Persons\Model\Profiles;
use Settings\Model\Settings;
use Zend\Filter\File\RenameUpload;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class ProfilesController extends AdminCore{

    public $entitySettings;
    public $entityUsers;
    public $entityProfiles;
    public $entityLeaveLists;
    public $entityPositions;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Users $entityUsers, Profiles $entityProfiles, LeaveLists $entityLeaveLists, Positions $entityPositions){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityUsers = $entityUsers;
        $this->entityProfiles = $entityProfiles;
        $this->entityLeaveLists = $entityLeaveLists;
        $this->entityPositions = $entityPositions;
    }

    public function indexAction(){
        $userIdLogin = $this->sessionContainer->id;
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        $arrProfiles = new Paginator(new ArrayAdapter($this->entityProfiles->fetchAlls($queries)));
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrProfiles->setCurrentPageNumber($page);
        $arrProfiles->setItemCountPerPage($perPage);
        $arrProfiles->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrProfiles' => $arrProfiles,
            'contentPaginator' => $contentPaginator,
            'optionsAvatar' => $this->entityUsers->fetchOptionAvatar(),
            'optionGender' => $this->entityUsers->fetchOptionGender(),
            'formSearch' => $formSearch,
            'optionPositions'=>$this->entityPositions->fetchAllByName(),
            'userIdLogin' => $userIdLogin,
        ]);
    }

    public function editAction(){
        $sessionContainer = new Container('session_login', $this->sessionManager);
        $valueCurrent = $this->entityProfiles->fetchRow($sessionContainer->id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('admin/users');
        }else{
            $valuePost = $valueCurrent;
            $valuePost['birthday'] = (isset($valuePost['birthday'])) ? date('d/m/Y',strtotime($valuePost['birthday'])) : '';
        }

        $form = new EditForm("edit");
        $optionPositions = $this->entityPositions->fetchAllByName();
        $form->get('position_id')->setValueOptions([''=>'---Chọn chức vụ---'] + $optionPositions);
        $form->get('position_id')->setAttributes(['disabled' => true]);
        $form->setData($valuePost);
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($valuePost);
            if($form->isValid()){
                $file = $request->getFiles()->toArray();
                $data = [
                    'firstname' => $valuePost['firstname'],
                    'lastname' => $valuePost['lastname'],
                    'gender' => $valuePost['gender'],
                ];
                if($valuePost['password'] != ''){
                    $data['password'] = md5($valuePost['password']);
                }
                if($file['avatar']['name'] != ''){
                    $data['avatar'] = $this->uploadFile($file, 'avatar');
                }
                $valuePost['birthday'] = (isset($valuePost['birthday'])) ? $valuePost['birthday'] : null;
                $birthday = date_create_from_format('d/m/Y H:i:s', $valuePost['birthday']);
                $dataProfiles = [
                    'name' => $valuePost['lastname'] . ' ' . $valuePost['firstname'],
                    'birthday' => (empty($birthday)) ? null : date_format($birthday, 'Y-m-d'),
                    'home_town' => $valuePost['home_town'],
                    'addresses' => $valuePost['addresses'],
                    'phones' => $valuePost['phones'],
                    'identity_cards' => $valuePost['identity_cards'],
                    'email' => $valuePost['email'],
                    'position_id' => $valuePost['position_id'],
                    'level_educations' => $valuePost['level_educations'],
                ];
                if($file['background_avatar']['name'] != ''){
                    $dataProfiles['background_avatar'] = $this->uploadFile($file, 'background_avatar');
                }
                $this->entityUsers->updateRow($sessionContainer->id, $data);
                $this->entityProfiles->updateRow($valueCurrent['user_id'],$dataProfiles);

                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                return $this->redirect()->toRoute('persons/profiles', ['action' => 'edit']);
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        return new ViewModel([
            'form' => $form,
            'valueCurrent' => $valueCurrent,
            'optionPositions' => $optionPositions
        ]);
    }

    private function uploadFile($file = null, $param = null){
        if($file == null || $param == null) return '';
        // \Zend\Debug\Debug::dump($file);
        $info = pathinfo($file[$param]['name']);
        // \Zend\Debug\Debug::dump($info); die();
        $url = 'users/' . $info['filename'] . '_' . time() . '.' . $info['extension'];
        $fileUpload = new RenameUpload([
            'target' => FILES_UPLOAD . $url,
            'randomize' => false
        ]);
        $fileUpload->filter($file[$param]);
        return $url;
    }

    public function seeAction(){
        $this->layout()->setTemplate('iframe/layout');
        $id = (int)$this->params()->fromRoute('id', 0);
        $arrProfiles = $this->entityProfiles->fetchRowAsId($id);
        $valueCurrent = $this->entityProfiles->fetchRow( $arrProfiles['user_id'] );
        //\Zend\Debug\Debug::dump($valueCurrent); die();
        $form = new EditForm("see");
        $optionPositions = $this->entityPositions->fetchAllByName();
        $form->get('position_id')->setValueOptions([''=>'---Chọn chức vụ---'] + $optionPositions);
        
        $form->setData($valueCurrent);
        foreach ($form->getElements() as $element) {
            if ($element instanceof \Zend\Form\Element\Text 
            || $element instanceof \Zend\Form\Element\Email
            || $element instanceof \Zend\Form\Element\Radio
            || $element instanceof \Zend\Form\Element\Select) {
                $element->setAttributes(['disabled' => true]);
            }
        }
        return new ViewModel([
            'form' => $form,
            'valueCurrent' => $valueCurrent,
            'optionPositions' => $optionPositions,
        ]);
    }

    public function editProfileAction(){
        // $this->layout()->setTemplate('iframe/layout');
        $id = (int)$this->params()->fromRoute('id', 0);
        $arrProfiles = $this->entityProfiles->fetchRowAsId($id);
        $valueCurrent = $this->entityProfiles->fetchRow($arrProfiles['user_id'] );
        if(empty($valueCurrent)){
            die('Not found!');
        }else{
            $settingDatas = json_decode($valueCurrent['datas'],true);
            $valuePost = $valueCurrent;
            $valuePost['birthday'] = (isset($valueCurrent['birthday'])) ? date('d/m/Y',strtotime($valueCurrent['birthday'])) : '';
            $valuePost['start_date'] = (isset($valueCurrent['start_date'])) ? date('d/m/Y H:i:s',strtotime($valueCurrent['start_date'])) : '';
            $valuePost['stop_date'] = (isset($valueCurrent['stop_date'])) ? date('d/m/Y H:i:s',strtotime($valueCurrent['stop_date'])) : '';
        }
        // \Zend\Debug\Debug::dump($settingDatas);die();
        $form = new EditForm("edit-profile",$settingDatas);
        $optionPositions = $this->entityPositions->fetchAllByName();
        $form->get('position_id')->setValueOptions([''=>'---Chọn chức vụ---'] + $optionPositions);
        $form->setData($valuePost);
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($valuePost);
            // \Zend\Debug\Debug::dump($valuePost);die();
            if($form->isValid()){
                $file = $request->getFiles()->toArray();
                $data = [
                    'firstname' => $valuePost['firstname'],
                    'lastname' => $valuePost['lastname'],
                    'gender' => $valuePost['gender'],
                ];
                if($valuePost['password'] != ''){
                    $data['password'] = md5($valuePost['password']);
                }
                if($file['avatar']['name'] != ''){
                    $data['avatar'] = $this->uploadFile($file, 'avatar');
                }
                $valuePost['birthday'] = (isset($valuePost['birthday'])) ? $valuePost['birthday'] : null;
                $birthday = date_create_from_format('d/m/Y H:i:s', $valuePost['birthday']);

                $valuePost['start_date'] = (isset($valuePost['start_date'])) ? $valuePost['start_date'] : null;
                $startDate = date_create_from_format('d/m/Y H:i:s', $valuePost['start_date']);

                $valuePost['stop_date'] = (isset($valuePost['stop_date'])) ? $valuePost['stop_date'] : null;
                $stopDate = date_create_from_format('d/m/Y H:i:s', $valuePost['stop_date']);
                $dataProfiles = [
                    'name' => $valuePost['lastname'] . ' ' . $valuePost['firstname'],
                    'birthday' => (empty($birthday)) ? null : date_format($birthday, 'Y-m-d'),
                    'home_town' => $valuePost['home_town'],
                    'addresses' => $valuePost['addresses'],
                    'phones' => $valuePost['phones'],
                    'identity_cards' => $valuePost['identity_cards'],
                    'email' => $valuePost['email'],
                    'position_id' => $valuePost['position_id'],
                    'level_educations' => $valuePost['level_educations'],
                    'start_date' => (empty($startDate)) ? null : date_format($startDate, 'Y-m-d H:i:s'),
                    'stop_date' => (empty($stopDate)) ? null : date_format($stopDate, 'Y-m-d H:i:s'),
                ];
                if($file['background_avatar']['name'] != ''){
                    $dataProfiles['background_avatar'] = $this->uploadFile($file, 'background_avatar');
                }
                $this->entityUsers->updateRow($valueCurrent['user_id'], $data);
                $this->entityProfiles->updateRow($valueCurrent['user_id'],$dataProfiles);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                return $this->redirect()->toRoute('persons/profiles', ['action' => 'edit-profile', 'id' => $valueCurrent['id']]);
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        return new ViewModel([
            'form' => $form,
            'valueCurrent' => $valueCurrent,
            'optionPositions' => $optionPositions,
            'id' => $id,
            'settingDatas' => $settingDatas,
        ]);
    }

    public function iframeSeeAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityProfiles->fetchRowAsId($id);
        if(empty($valueCurrent)){
            die('success');
        }
        return new ViewModel([
            'id' => $id
        ]);
    }

    public function addFieldAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = (int)$this->params()->fromRoute('id', 0);
        $request = $this->getRequest();
        $form = new AddFieldForm($request->getRequestUri());
        $valueCurrent = $this->entityProfiles->fetchRowAsId($id);
        if(empty($valueCurrent)){
            die("Not found!");
        }
        $arrProfileDatas = json_decode($valueCurrent['datas'],true);
        if($arrProfileDatas == null){
            $arrProfileDatas =[];
        }
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $dataPost = [];
            if($form->isValid()){
                $key = $this->getFirstCharacters($valuePost['name']). time();
                $dataPost[$key] = [
                    'name' => $valuePost['name'],
                    'content' => $valuePost['content'],
                ];
                $data = array_merge($arrProfileDatas, $dataPost); 
                $datas = [
                    'datas' => json_encode($data),
                ];
                $this->entityProfiles->updateRow($valueCurrent['user_id'],$datas);
                die("success");
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function editFieldAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = (int)$this->params()->fromRoute('id', 0);
        $datasKey = $this->params()->fromRoute('datas_key','');
        $valueCurrent = $this->entityProfiles->fetchRowAsId($id);
        if(empty($valueCurrent)){
            die("Not found!");
        }
        $arrDatasKey = json_decode($valueCurrent['datas'],true);
        if(!isset($arrDatasKey[$datasKey])){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('success');
        }
        $valuePost = $arrDatasKey[$datasKey];
        $request = $this->getRequest();
        $form = new EditFieldForm($request->getRequestUri());
        $form->setData($valuePost);
        // \Zend\Debug\Debug::dump($arrDatasKey[$datasKey]);die();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $arrDatasKey[$datasKey] = [
                    'name' => $valuePost['name'],
                    'content' => $valuePost['content'],
                ];
                $this->entityProfiles->updateRow($valueCurrent['user_id'],['datas' => json_encode($arrDatasKey)]);
                die("success");
            }
        }
        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function deleteFieldAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = (int)$this->params()->fromRoute('id', 0);
        $datasKey = $this->params()->fromRoute('datas_key','');
        $valueCurrent = $this->entityProfiles->fetchRowAsId($id);
        if(empty($valueCurrent)){
            die("Not found!");
        }
        $arrDatasKey = json_decode($valueCurrent['datas'],true);
        if(!isset($arrDatasKey[$datasKey])){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('success');
        }
        $request = $this->getRequest();
        $form = new DeleteFieldForm($request->getRequestUri());
        // \Zend\Debug\Debug::dump($arrDatasKey[$datasKey]);die();
        if($request->isPost()){
            unset($arrDatasKey[$datasKey]);
            $this->entityProfiles->updateRow($valueCurrent['user_id'],['datas' => json_encode($arrDatasKey)]);
            die("success");
        }
        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function getFirstCharacters($string){
        $getString = \Pxt\String\ChangeString::stripUnicode($string);
        $words = explode(" ", $getString);
        $result = "";
        foreach ($words as $w) {
            $result .= $w[0];
        }
        return $result;
    }
}