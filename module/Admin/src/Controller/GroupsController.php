<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Admin\Model\Groups;
use Admin\Form\Groups\AddForm;
use Admin\Form\Groups\EditForm;
use Admin\Form\Groups\DeleteForm;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Admin\Model\Mcas;
use Admin\Model\McasGroups;
use Zend\Session\Container;
use Admin\Model\GroupsPositionsUsers;
use Admin\Model\McasUsers;
use Admin\Form\Groups\SearchForm;
use Admin\Model\McasUsersDeny;
use Settings\Model\Companies;

class GroupsController extends AdminCore{
    public $entityGroups;
    public $entitySettings;
    public $entityMcas;
    public $entityMcasGroups;
    public $entityGroupsPositionsUsers;
    public $entityMcasUsers;
    public $entityCompanies;
    public $entityMcasUsersDeny;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, Groups $entityGroups, Mcas $entityMcas, 
        McasGroups $entityMcasGroups, GroupsPositionsUsers $entityGroupsPositionsUsers, McasUsers $entityMcasUsers, Companies $entityCompanies, McasUsersDeny $entityMcasUsersDeny) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityGroups = $entityGroups;
        $this->entityMcas = $entityMcas;
        $this->entityMcasGroups = $entityMcasGroups;
        $this->entityGroupsPositionsUsers = $entityGroupsPositionsUsers;
        $this->entityMcasUsers = $entityMcasUsers;
        $this->entityCompanies = $entityCompanies;
        $this->entityMcasUsersDeny = $entityMcasUsersDeny;
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
        // reset company_id if empty
        if(!isset($queries['company_id']) || $queries['company_id'] == ''){
            $queries['company_id'] = isset($queries['company_id']) ? $queries['company_id'] : $this->defineCompanyId;
        }
        $formSearch->setData($queries);
        
        $arrGroups = new Paginator(new ArrayAdapter( $this->entityGroups->fetchAlls($queries) ));
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        //\Zend\Debug\Debug::dump($contentPaginator); die();
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrGroups->setCurrentPageNumber($page);
        $arrGroups->setItemCountPerPage($perPage);
        $arrGroups->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrGroups' => $arrGroups, 
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'userId' => $this->sessionContainer->id
        ]);
    }
    
    public function addAction(){
        $view = new ViewModel();
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new AddForm($request->getRequestUri(), $this->sessionContainer->id);
        
        if($this->sessionContainer->id == '1'){
            $arrCompanies = $this->entityCompanies->fetchAllToOptions();
            $form->get('company_id')->setValueOptions( [
                '' => '--- Chọn một công ty ---'] + 
                $arrCompanies 
            );
        }
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            // \Zend\Debug\Debug::dump($valuePost);die();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'name' => $valuePost['name'],
                    'level' => $valuePost['level'],
                    'description' => $valuePost['description'],
                    'url_upload_file' => "fortune",
                    'status' => $valuePost['status'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                if($this->sessionContainer->id == '1'){
                    $data['company_id'] = $valuePost['company_id'];
                }else{
                    $data['company_id'] = '';
                }
                $this->entityGroups->addRow($data);
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
    
    public function editAction(){
        $view = new ViewModel();
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityGroups->fetchRow($id);
        
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('admin/groups');
        }else{
            $valuePost = $valueCurrent; 
        }
        
        $form = new EditForm($request->getRequestUri(), $this->sessionContainer->id);
        if($this->sessionContainer->id == '1'){
            $arrCompanies = $this->entityCompanies->fetchAllToOptions();
            $form->get('company_id')->setValueOptions( [
                '' => '--- Chọn một công ty ---'] + 
                $arrCompanies 
            );
            $form->get('company_id')->setAttribute('readonly', 'readonly');
        }
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'name' => $valuePost['name'],
                    'level' => $valuePost['level'],
                    'description' => $valuePost['description'],
                    'status' => $valuePost['status'],
                ];
                $this->entityGroups->updateRow($id, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
                //return $this->redirect()->toRoute('admin/users');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }else{
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
        $valueCurrent = $this->entityGroups->fetchRow($id);
        if(empty($valueCurrent)){
            die('success');
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];
//         $arrGroupsPositionsUsers = $this->entityGroupsPositionsUsers->fetchAllAsGroupsId($id);
//         $arrMcasGroups = $this->entityMcasGroups->fetchAllAsGroupsId($id);
//         if($arrGroupsPositionsUsers){
//             $checkRelationship['groups_positions_users'] = 1;
//         }
//         if($arrMcasGroups){
//             $checkRelationship['mcas_groups'] = 1;
//         }

        if($request->isPost()){
            // delete permission deny in 'mcas_users'
//             if(!empty($arrGroupsPositionsUsers)){
//                 foreach($arrGroupsPositionsUsers as $itemGroupsPositionsUsers){
//                     if(!empty($arrMcasGroups)){
//                         foreach($arrMcasGroups as $itemMcasGroups){
//                             $this->entityMcasUsers->deleteRows(['users_id' => $itemGroupsPositionsUsers['users_id'], 'mcas_id' => $itemMcasGroups['mcas_id']]);
//                         }
//                     }
//                 }
//             }
//             // delete all groups in table
//             $this->entityGroupsPositionsUsers->deleteRows(['groups_id' => $id]);
            // delete all permissions groups
//             $this->entityMcasGroups->deleteRows(['groups_id' => $id]);
            $this->entityGroups->updateRow($id, ['status' => '-1']);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form, 
            'checkRelationship' => $checkRelationship, 
            'valueCurrent' => $valueCurrent
        ]);
    }
    
    public function permissionsAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $groupId = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityGroups->fetchRow($groupId);
        
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('admin/groups');
        }else{
            $valuePost = $valueCurrent; 
        }
        // get list mvc
        $sessionContainer = new Container('session_login', $this->sessionManager);
        $arrMcas = $this->entityMcas->fetchAllAsLevel(1, null, $sessionContainer->permissions);
        //\Zend\Debug\Debug::dump($arrMcas); die();
        
        // get permission in groups and build structure: ['mcas_id-groups_id' => 'on or off'] 
        $mcasGroupsCurrent = $this->pairIdFromAvailable($this->entityMcasGroups->fetchRowAsGroups($groupId));
        // \Zend\Debug\Debug::dump($mcasGroupsCurrent); die();

        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
        //\Zend\Debug\Debug::dump($valuePost);
            //die();
            $resultValuePost = $this->splitIdFromCheckbox($valuePost, $groupId);
            if(!empty($arrMcas)){
                // get all users relationship with groups
                $arrUsers = $this->entityGroupsPositionsUsers->fetchAllAsGroupsId($groupId);
                // \Zend\Debug\Debug::dump($arrUsers); die();
                foreach($arrMcas as $item){
                    if(!empty($item['child'])){
                        foreach($item['child'] as $item1){
                            $key = $item1['id'] . '-' . $groupId;
                            if(isset($resultValuePost[$key])){
                                // check not exist then add, else then not change
                                if(!isset($mcasGroupsCurrent[$key])){
                                    $this->entityMcasGroups->addRow($resultValuePost[$key]);
                                }
                            }elseif(isset($mcasGroupsCurrent[$key])){
                                //\Zend\Debug\Debug::dump($mcasGroupsCurrent[$key]); die();
                                // delete permission deny in mcas_users
                                if(!empty($arrUsers)){
                                    foreach($arrUsers as $itemUser){
                                        //$this->entityMcasUsers->deleteRows(['user_id' => $itemUser['user_id'], 'mca_id' => $mcasGroupsCurrent[$key]['mca_id']]);
                                        $this->entityMcasUsersDeny->deleteRow(['user_id' => $itemUser['user_id'], 'mca_id' => $mcasGroupsCurrent[$key]['mca_id']]);
                                    }
                                }
                                $this->entityMcasGroups->deleteRow($mcasGroupsCurrent[$key]);
                            }
                        }
                    }
                }
            }
            // reset mcasGroupsCurrent
            $mcasGroupsCurrent = $this->pairIdFromAvailable($this->entityMcasGroups->fetchRowAsGroups($groupId));
            $this->flashMessenger()->addSuccessMessage('Phân quyền thành công!');
        }
        
        return new ViewModel(['arrMcas' => $arrMcas, 'valueCurrent' => $valueCurrent, 'mcasGroupsCurrent' => $mcasGroupsCurrent]);
    }
    public function splitIdFromCheckbox($valuePost, $groupId){
        $result = [];
        if(!empty($valuePost)){
            foreach($valuePost as $key => $item){
                // split key '-'
                $arrKey = explode('-', $key);
                if(isset($arrKey[1])){
                    $result[$arrKey[1] . '-' . $groupId] = [
                        'mca_id' => $arrKey[1], 
                        'group_id' => $groupId, 
                        'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                    ];
                }
            }
        }
        return $result;
    }
    public function pairIdFromAvailable($arrMcasGroups){
        //\Zend\Debug\Debug::dump($arrMcasGroups); die();
        $result = [];
        if(!empty($arrMcasGroups)){
            foreach($arrMcasGroups as $item){
                $result[$item['mca_id'] . '-' . $item['group_id']] = [
                    'mca_id' => $item['mca_id'],
                    'group_id' => $item['group_id']
                ];
            }
        }
        return $result;
    }// Tạo bảng Mcas


    /**
     * create data mcas
     */
    public function createMcasAction(){ die('not begin');
        $data = [[ 'code' => 'statistics/search', 'name' => 'Tra cứu mã PIN',
            'child' => [
                [ 'code' => 'index', 'name' => 'Tra cứu mã PIN' ],
                [ 'code' => 'iframe', 'name' => 'Bảo hành' ]
            ]
        ],[ 'code' => 'statistics/warranties', 'name' => 'Bảo hành',
            'child' => [
                [ 'code' => 'index', 'name' => 'Thông tin bảo hành' ],
                [ 'code' => 'add', 'name' => 'Thêm bảo hành' ],
                [ 'code' => 'edit', 'name' => 'Sửa bảo hành' ],
                [ 'code' => 'delete', 'name' => 'Xóa bảo hành' ]
            ]
        ],[ 'code' => 'products/imports', 'name' => 'Nhập kho',
            'child' => [
                [ 'code' => 'index', 'name' => 'Nhập kho' ],
                [ 'code' => 'iframe', 'name' => 'Nhập mã PIN vào kho' ]
            ]
        ],[ 'code' => 'statistics/index', 'name' => 'Thống kê tin nhắn',
            'child' => [
                [ 'code' => 'index', 'name' => 'Ds tin nhắn kiểm tra' ]
            ]
        ],[ 'code' => 'statistics/promotions', 'name' => 'Thống kê khuyến mãi',
            'child' => [
                [ 'code' => 'index', 'name' => 'Ds tham gia khuyến mãi' ],
                [ 'code' => 'winner', 'name' => 'Ds trúng thưởng khuyến mãi' ]
            ]
        ],[ 'code' => 'statistics/dials', 'name' => 'Thống kê quay số',
            'child' => [
                [ 'code' => 'index', 'name' => 'Ds tham gia quay số' ],
                [ 'code' => 'winner', 'name' => 'Ds trúng thưởng quay số' ]
            ]
        ],];
        foreach($data as $item){
            $dataInsert = [
                'level' => '1',
                'code' => $item['code'],
                'name' => $item['name'],
                'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
            ];
            $parentId = $this->entityMcas->addRow($dataInsert);
            if(!empty($item['child'])){
                foreach($item['child'] as $itemChild){
                    $dataInsertChild = [
                        'level' => '2',
                        'parent_id' => $parentId,
                        'code' => $itemChild['code'],
                        'name' => $itemChild['name'],
                        'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                    ];
                    $this->entityMcas->addRow($dataInsertChild);
                }
            }
        }
        die('finished!');
    }
}
