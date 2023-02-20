<?php

namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Admin\Form\Users\AddForm;
use Admin\Form\Users\EditForm;
use Admin\Core\AdminCore;
use Admin\Model\Users;
use Admin\Model\PxtAuthentication;
use Admin\Form\Users\DeleteForm;
use Settings\Model\Settings;
use Admin\Form\Users\ChangePasswordForm;
use Zend\Session\Container;
use Admin\Form\Users\GroupsAddForm;
use Admin\Model\Groups;
use Admin\Model\Positions;
use Admin\Model\GroupsPositionsUsers;
use Admin\Model\Mcas;
use Admin\Form\Users\SearchForm;
use Admin\Form\Users\ProfileForm;
use Zend\Filter\File\RenameUpload;
use Settings\Model\Companies;
use Admin\Model\McasUsersDeny;
use Admin\Model\McasUsersAllow;
use Persons\Model\LeaveLists;
use Persons\Model\Profiles;

class UsersController extends AdminCore{
    public $entityUsers;
    public $entitySettings;
    public $entityGroups;
    public $entityPositions;
    public $entityGroupsPositionsUsers;
    public $entityMcasUsersDeny;
    public $entityMcasUsersAllow;
    public $entityMcas;
    public $entityCompanies;
    public $entityProfiles;
    public $entityLeaveLists;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, 
        Users $entityUsers, Groups $entityGroups, Positions $entityPositions, 
        GroupsPositionsUsers $entityGroupsPositionsUsers, Mcas $entityMcas, McasUsersDeny $entityMcasUsersDeny, 
        McasUsersAllow $entityMcasUsersAllow, Companies $entityCompanies, Profiles $entityProfiles, LeaveLists $entityLeaveLists ) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityUsers = $entityUsers;
        $this->entityGroups = $entityGroups;
        $this->entityPositions = $entityPositions;
        $this->entityGroupsPositionsUsers = $entityGroupsPositionsUsers;
        $this->entityMcasUsersDeny = $entityMcasUsersDeny;
        $this->entityMcasUsersAllow = $entityMcasUsersAllow;
        $this->entityMcas = $entityMcas;
        $this->entityCompanies = $entityCompanies;
        $this->entityProfiles = $entityProfiles;
        $this->entityLeaveLists = $entityLeaveLists;
    }
    
    public function indexAction(){
        $formSearch = new SearchForm('index', $this->sessionContainer->id);
        
        if($this->sessionContainer->id == '1'){
            $arrCompanies = $this->entityCompanies->fetchAllToOptions();
            $formSearch->get('company_id')->setValueOptions( [
                '' => '--- Chọn một công ty ---'] + 
                $arrCompanies 
            );
        }
        
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        
        // reset company_id if empty
        if(!isset($queries['company_id']) || $queries['company_id'] == ''){
            $queries['company_id'] = isset($queries['company_id']) ? $queries['company_id'] : $this->defineCompanyId;
            //echo $queries['company_id']; die();
        }
        
        $sessionContainer = new Container('session_login', $this->sessionManager);
       // \Zend\Debug\Debug::dump($sessionContainer->getArrayCopy()); die();
        //\Zend\Debug\Debug::dump($this->defineCompanyId); die();
        
        $arrUsers = new Paginator(new ArrayAdapter( $this->entityUsers->fetchAlls(['groups_id' => $sessionContainer->groups_id] + $queries)));
        
        
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        //\Zend\Debug\Debug::dump($contentPaginator); die();
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrUsers->setCurrentPageNumber($page);
        $arrUsers->setItemCountPerPage($perPage);
        $arrUsers->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrUsers' => $arrUsers, 
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'userId' => $this->sessionContainer->id,
            'optionCompanies' => $this->entityCompanies->fetchAllToOptions(),
            'companyIsGroups' => $this->entityCompanies->fetchAllToOptions1()
        ]);
    }
    
    public function addAction(){
        //die($this->sessionContainer->company_id);
        $view = new ViewModel();
        $form = new AddForm('add', $this->sessionContainer->id);
        
        if($this->sessionContainer->id == '1'){
            $arrCompanies = $this->entityCompanies->fetchAllToOptions();
            $form->get('company_id')->setValueOptions( ['' => '--- Chọn một công ty ---'] + $arrCompanies 
            );
        }
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            //$valuePost['company_id'] = null;
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if($this->entityUsers->fetchRowAsUsername($valuePost['username'])){
                $form->get('username')->setMessages($form->get('username')->getMessages() + ['username_exist' => 'Tài khoản đăng nhập đã được tạo trước đó!']);
                $isValid = false;
            }
            if($isValid){
                $data = [
                    'username' => $valuePost['username'],
                    'firstname' => $valuePost['firstname'],
                    'lastname' => $valuePost['lastname'],
                    'password' => md5($valuePost['password']),
                    'gender' => $valuePost['gender'],
                    'status' => '1',
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $data['configs'] = json_encode([
                    'view_all_agent'        => isset($valuePost['view_all_agent']) ? $valuePost['view_all_agent'] : "0",
                    'view_all_storehouse'   => isset($valuePost['view_all_storehouse']) ? $valuePost['view_all_storehouse'] : "0"
                ]);
                
                if($this->sessionContainer->id == '1'){
                    $data['company_id'] = $valuePost['company_id'];
                }else{
                    $data['company_id'] = $this->sessionContainer->company_id;
                }
                $fetchUserId = $this->entityUsers->addRow($data);
                if($data['company_id'] == '' || $data['company_id'] == null){
                    $dataProfiles = [
                        'name' => $valuePost['lastname'] . ' ' . $valuePost['firstname'],
                        'user_id' => $fetchUserId,
                        'start_date' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                    ];
                    $profile = $this->entityProfiles->addRow($dataProfiles);
                    $dataLeaves = [
                        'user_id' => $fetchUserId,
                        'profile_id' => $profile
                    ];
                    $this->entityLeaveLists->addRow($dataLeaves);
                }
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                return $this->redirect()->toRoute('admin/users');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $view->setVariable('form', $form);
        $view->setVariable('userId', $this->sessionContainer->id);
        return $view;
    }
    
    public function editAction(){
        $view = new ViewModel();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityUsers->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('admin/users');
        }else{
            $valuePost = $valueCurrent; 
            // \Zend\Debug\Debug::dump($valuePost); die();
        }
        
        $form = new EditForm('edit', $this->sessionContainer->id);
        //\Zend\Debug\Debug::dump($form); die();
        if($this->sessionContainer->id == '1'){
            $arrCompanies = $this->entityCompanies->fetchAllToOptions();
            $form->get('company_id')->setValueOptions( [
                '' => '--- Chọn một công ty ---'] + 
                $arrCompanies 
            );
        }
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            //\Zend\Debug\Debug::dump($valuePost); die();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                   // 'username' => $valuePost['username'],
                    'firstname' => $valuePost['firstname'],
                    'lastname'  => $valuePost['lastname'],
                    'gender'    => $valuePost['gender'],
                ];
                $data['configs'] = json_encode([
                    'view_all_agent'        => isset($valuePost['view_all_agent']) ? $valuePost['view_all_agent'] : "0",
                    'view_all_storehouse'   => isset($valuePost['view_all_storehouse']) ? $valuePost['view_all_storehouse'] : "0"
                ]);
                $this->entityUsers->updateRow($id, $data);
                // \Zend\Debug\Debug::dump($valueCurrent); die();
                if($valueCurrent['company_id'] == "" || $valueCurrent['company_id'] == null){
                    $dataProfiles = [
                        'name' => $valuePost['lastname'] . ' ' . $valuePost['firstname'],
                    ];
                    $this->entityProfiles->updateRow($id, $dataProfiles);
                }
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }else{
            $configs = json_decode($valueCurrent['configs'], true);
            if(isset($configs['view_all_agent'])){
                $valuePost['view_all_agent'] = $configs['view_all_agent'];
            }
            $form->setData($valuePost);
        }
        
        $view->setVariable('userId', $this->sessionContainer->id);
        $view->setVariable('form', $form);
        return $view;
    }
    
    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityUsers->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('admin/users');
        }

        // check users logining
        $sessionContainer = new Container('session_login', $this->sessionManager);
        if($valueCurrent['username'] == $sessionContainer->username){
            $this->flashMessenger()->addWarningMessage('Bạn không thể xoá chính mình!');
            die('success');
        }
        
        $form = new DeleteForm($request->getRequestUri());
        
        if($request->isPost()){
            $data = ['status' => '-1'];
            $this->entityUsers->updateRow($id, $data);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form,
            'valueCurrent' => $valueCurrent
        ]);
    }
    
    public function changePasswordAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityUsers->fetchRow($id);
        
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('admin/users');
        }else{
            $valuePost = $valueCurrent; 
        }
        $form = new ChangePasswordForm($request->getRequestUri());
        $form->setData($valuePost);
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'password' => md5($valuePost['password']),
                ];
                $this->entityUsers->updateRow($id, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
                //return $this->redirect()->toRoute('admin/users');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        
        return new ViewModel(['form' => $form]);
    }
    
    public function groupsAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityUsers->fetchRow($id);
        
        if(empty($valueCurrent)){
            die('success');
        }else{
            $valuePost = $valueCurrent; 
        }

        $sessionContainer = new Container('session_login', $this->sessionManager);
        if($valueCurrent['id'] == $sessionContainer->id){
            $this->flashMessenger()->addWarningMessage('Bạn không thể phân phòng ban cho chính mình!');
            die('success');
        }
        
        /* $form = new GroupsForm($request->getRequestUri());
        $form->setData($valuePost);
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'password' => md5($valuePost['password']),
                ];
                $this->entityUsers->updateRow($id, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
                //return $this->redirect()->toRoute('admin/users');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        } */
        
        return new ViewModel(['valueCurrent' => $valueCurrent]);
    }
    
    public function groupsViewAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityUsers->fetchRow($id);
        
        if(empty($valueCurrent)){
            die('success');
        }else{
            $valuePost = $valueCurrent; 
        }
        // get data groups, positions
        $arrGroupsPositionsUsers = $this->entityGroupsPositionsUsers->fetchAllAsUsersId(['user_id' => $id]);
        //\Zend\Debug\Debug::dump($arrGroupsPositionsUsers); die();
        $arrGroups = $this->entityGroups->fetchAllOptions(['company_id' => $valueCurrent['company_id']]);
        
        $arrPositions = $this->entityPositions->fetchAllOptions(['company_id' => $valueCurrent['company_id']]);
        
        return new ViewModel(['valueCurrent' => $valueCurrent, 
            'arrGroupsPositionsUsers' => $arrGroupsPositionsUsers,
            'arrGroups' => $arrGroups,
            'arrPositions' => $arrPositions
        ]);
    }
    
    public function groupsAddAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $userId = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityUsers->fetchRow($userId);
        
        if(empty($valueCurrent)){
            die('error-not-found');
        }
        
        $form = new GroupsAddForm($request->getRequestUri());

        $arrGroups = $this->entityGroups->fetchAllOptions(['company_id' => $valueCurrent['company_id']]);
        $form->get('groups_id')->setValueOptions( ['0' => '--- Chọn phòng ban ---' ] + $arrGroups );
        
        $arrPositions = $this->entityPositions->fetchAllOptions(['company_id' => $valueCurrent['company_id']]);
        $form->get('positions_id')->setValueOptions( ['0' => '--- Chọn chức vụ ---' ] + $arrPositions );
        //\Zend\Debug\Debug::dump($arrGroups); die();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            // check error select
            $currentGroups = $this->entityGroups->fetchRow($valuePost['groups_id']);
            if(empty($currentGroups)){
                $this->flashMessenger()->addWarningMessage('Chưa chọn phòng ban!');
            }else{
                // check error select
                $currentPositions = $this->entityPositions->fetchRow($valuePost['positions_id']);
                if(empty($currentPositions)){
                    $this->flashMessenger()->addWarningMessage('Chưa chọn chức vụ!');
                }else{
                    // check groups exist
                    $checkUsersIdInGroups = $this->entityGroupsPositionsUsers->checkUsersIdInGroups($valuePost['groups_id'], $userId);
                    if(!empty($checkUsersIdInGroups)){
                        $this->flashMessenger()->addWarningMessage($valueCurrent['username'] . ' đã tồn tại trong phòng ban "' . $arrGroups[$checkUsersIdInGroups['group_id']] . '" với chức vụ "' . $arrPositions[$checkUsersIdInGroups['position_id']] . '"!');
                    }else{
                        $data = [
                            'group_id' => $valuePost['groups_id'],
                            'position_id' => $valuePost['positions_id'],
                            'user_id' => $userId,
                            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                        ];
                        $a = $this->entityGroupsPositionsUsers->addRow($data);
                        //\Zend\Debug\Debug::dump($a); die();
                        $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                    }
                    die('success');
                }
            }
        }
        return new ViewModel(['form' => $form]);
    }
    
    public function groupsDeleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $queries = $this->params()->fromQuery();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $groupsId = $queries['group_id'];
        $positionsId = $queries['position_id'];
        
        $data = ['status' => '-1', 'deleted_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()];
        
        $valueCurrent = $this->entityGroupsPositionsUsers->updateRows(['user_id' => $id, 'group_id' => $groupsId, 'position_id' => $positionsId], $data);
        $this->flashMessenger()->addSuccessMessage('Xóa phân phòng ban thành công!');
        die('success');
    }
    
    public function permissionsDenyAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $userId = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityUsers->fetchRow($userId);
        
        if(empty($valueCurrent)){
            die('success');
        }else{
            $valuePost = $valueCurrent; 
        }
        $sessionContainer = new Container('session_login', $this->sessionManager);
        if($valueCurrent['id'] == $sessionContainer->id){
            $this->flashMessenger()->addWarningMessage('Bạn không thể chặn quyền cho chính mình!');
            die('success');
        }
        // check company can deny?
        $currentCompany = $this->entityCompanies->fetchRow($valueCurrent['company_id']);
        if(!empty($currentCompany)){
            if($currentCompany['is_group'] == "0"){
                $this->flashMessenger()->addWarningMessage('Bạn không thể chặn quyền khi công ty không có tính năng này!');
                die('success');
            }
        }
        // get list mvc
        $arrMcas = $this->entityMcas->fetchAllAsLevel(1, null, $sessionContainer->permissions);
        //\Zend\Debug\Debug::dump($arrMcas); die();
        
       // get permission in groups and build structure: ['mcas_id-groups_id' => 'on or off'] 
       $currentMcasUsersDeny = $this->pairIdFromAvailable($this->entityMcasUsersDeny->fetchRowAsUser($userId));
       //\Zend\Debug\Debug::dump($mcasGroupsCurrent); die();
        
        if($request->isPost()){
            //die('da post');
            $valuePost = $request->getPost()->toArray();
            $resultValuePost = $this->splitIdFromCheckbox($valuePost, $userId);
            //\Zend\Debug\Debug::dump($resultValuePost);
            //die();
            if(!empty($arrMcas)){
                foreach($arrMcas as $item){
                    if(!empty($item['child'])){
                        foreach($item['child'] as $item1){
                            $key = $item1['id'] . '-' . $userId;
                            if(isset($resultValuePost[$key])){
                                // check not exist then add, else then not change
                                if(!isset($currentMcasUsersDeny[$key])){
                                    $this->entityMcasUsersDeny->addRow($resultValuePost[$key]);
                                }
                            }elseif(isset($currentMcasUsersDeny[$key])){
                                $this->entityMcasUsersDeny->deleteRow($currentMcasUsersDeny[$key]);
                            }
                        }
                    }
                }
            }
            // reset mcasGroupsCurrent
            $currentMcasUsersDeny = $this->pairIdFromAvailable($this->entityMcasUsersDeny->fetchRowAsUser($userId));
            $this->flashMessenger()->addSuccessMessage('Chặn quyền thành công!');
        }
        
        return new ViewModel([
            'arrMcas' => $arrMcas, 
            'valueCurrent' => $valueCurrent, 
            'currentMcasUsersDeny' => $currentMcasUsersDeny
        ]);
    }
    
    public function permissionsAllowAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $userId = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityUsers->fetchRow($userId);
        
        if(empty($valueCurrent)){
            die('success');
        }else{
            $valuePost = $valueCurrent; 
        }
        $sessionContainer = new Container('session_login', $this->sessionManager);
        if($valueCurrent['id'] == $sessionContainer->id){
            $this->flashMessenger()->addWarningMessage('Bạn không thể phân quyền cho chính mình!');
            die('success');
        }
        // check company can deny?
        $currentCompany = $this->entityCompanies->fetchRow($valueCurrent['company_id']);
        if(!empty($currentCompany)){
            if($currentCompany['is_group'] == "1"){
                $this->flashMessenger()->addWarningMessage('Bạn không thể phân quyền khi công ty không có tính năng này!');
                die('success');
            }
        }
        // get list mvc
        $arrMcas = $this->entityMcas->fetchAllAsLevel(1, null, $sessionContainer->permissions);
        //\Zend\Debug\Debug::dump($arrMcas); die();
        
       // get permission in groups and build structure: ['mcas_id-groups_id' => 'on or off'] 
       $currentMcasUsersAllow = $this->pairIdFromAvailable($this->entityMcasUsersAllow->fetchRowAsUser($userId));
    //    \Zend\Debug\Debug::dump($currentMcasUsersAllow); die();
        
        if($request->isPost()){
            //die('da post');
            $valuePost = $request->getPost()->toArray();
            \Zend\Debug\Debug::dump($valuePost);
            $resultValuePost = $this->splitIdFromCheckbox($valuePost, $userId);
            //\Zend\Debug\Debug::dump($resultValuePost); die();
            //die();
            if(!empty($arrMcas)){
                foreach($arrMcas as $item){
                    if(!empty($item['child'])){
                        foreach($item['child'] as $item1){
                            $key = $item1['id'] . '-' . $userId;
                            if(isset($resultValuePost[$key])){
                                // check not exist then add, else then not change
                                if(!isset($currentMcasUsersAllow[$key])){
                                    $this->entityMcasUsersAllow->addRow($resultValuePost[$key]);
                                }
                            }elseif(isset($currentMcasUsersAllow[$key])){
                                $this->entityMcasUsersAllow->deleteRow($currentMcasUsersAllow[$key]);
                            }
                        }
                    }
                }
            }
            // reset mcasGroupsCurrent
            $currentMcasUsersAllow = $this->pairIdFromAvailable($this->entityMcasUsersAllow->fetchRowAsUser($userId));
            $this->flashMessenger()->addSuccessMessage('Phân quyền thành công!');
        }
        
        return new ViewModel([
            'arrMcas' => $arrMcas, 
            'valueCurrent' => $valueCurrent, 
            'currentMcasUsersAllow' => $currentMcasUsersAllow
        ]);
    }
    
    public function splitIdFromCheckbox($valuePost, $userId){
        $result = [];
        if(!empty($valuePost)){
            foreach($valuePost as $key => $item){
                // split key '-'
                $arrKey = explode('-', $key);
                if(isset($arrKey[1])){
                    $result[$arrKey[1] . '-' . $userId] = [
                        'mca_id' => $arrKey[1], 
                        'user_id' => $userId, 
                        'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                    ];
                }
            }
        }
        return $result;
    }
    
    public function pairIdFromAvailable($arrMcasUsers){
        $result = [];
        if(!empty($arrMcasUsers)){
            foreach($arrMcasUsers as $item){
                $result[$item['mca_id'] . '-' . $item['user_id']] = [
                    'mca_id' => $item['mca_id'],
                    'user_id' => $item['user_id']
                ];
            }
        }
        return $result;
    }
    
    public function profileAction(){
        $sessionContainer = new Container('session_login', $this->sessionManager);

        $valueCurrent = $this->entityUsers->fetchRow($sessionContainer->id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('admin/users');
        }else{
            $valuePost = $valueCurrent;
        }

        $form = new ProfileForm();
        $form->setData($valuePost);
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'firstname' => $valuePost['firstname'],
                    'lastname' => $valuePost['lastname'],
                    'gender' => $valuePost['gender'],
                ];
                if($valuePost['password'] != ''){
                    $data['password'] = md5($valuePost['password']);
                }
                // upload avatar
                $file = $request->getFiles()->toArray();
                if($file['avatar']['name'] != ''){
                    $data['avatar'] = $this->uploadFile($file, 'avatar');
                }
                $this->entityUsers->updateRow($sessionContainer->id, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                return $this->redirect()->toRoute('admin/users', ['action' => 'profile']);
            }else{
                //\Zend\Debug\Debug::dump($form); die();
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        return new ViewModel(['form' => $form, 'valuePost' => $valuePost]);
    }
    
    private function uploadFile($file = null, $param = null){
        if($file == null || $param == null) return '';
        $info = pathinfo($file[$param]['name']);
        $url = 'users/' . $info['filename'] . '_' . time() . '.' . $info['extension'];
        $fileUpload = new RenameUpload([
            'target' => FILES_UPLOAD . $url,
            'randomize' => false
        ]);
        $fileUpload->filter($file[$param]);
        return $url;
    }
    
    public function iframeAddressesAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityUsers->fetchRow($id);
        //\Zend\Debug\Debug::dump($valueCurrent); die();
        if(empty($valueCurrent)){
            die('success');
        }
        $view->setVariable('id', $id);
        return $view;
    }
    
    public function iframePhonesAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityUsers->fetchRow($id);
        //\Zend\Debug\Debug::dump($valueCurrent); die();
        if(empty($valueCurrent)){
            die('success');
        }
        $view->setVariable('id', $id);
        return $view;
    }
    
    public function iframeIdentityCardsAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityUsers->fetchRow($id);
        //\Zend\Debug\Debug::dump($valueCurrent); die();
        if(empty($valueCurrent)){
            die('success');
        }
        $view->setVariable('id', $id);
        return $view;
    }

    public function constractAction(){
        $this->layout()->setTemplate('empty/layout');
        return new ViewModel();
    }
    
}
