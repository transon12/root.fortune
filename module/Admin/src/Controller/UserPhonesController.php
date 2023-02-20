<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Admin\Model\Users;
use Admin\Model\UserPhones;
use Admin\Form\UserPhones\AddForm;
use Admin\Form\UserPhones\EditForm;
use Admin\Form\UserPhones\DeleteForm;

class UserPhonesController extends AdminCore{
    public $entityUserPhones;
    public $entitySettings;
    public $entityUsers;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, 
        UserPhones $entityUserPhones, Users $entityUsers) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityUserPhones = $entityUserPhones;
        $this->entityUsers = $entityUsers;
    }
    
    public function indexAction(){
        $userId = (int)$this->params()->fromRoute('id', 0);
        $this->checkUserId($userId);
        $this->layout()->setTemplate('iframe/layout');
        $arrUserPhones = new Paginator(new ArrayAdapter( $this->entityUserPhones->fetchAlls(['user_id' => $userId]) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrUserPhones->setCurrentPageNumber($page);
        $arrUserPhones->setItemCountPerPage($perPage);
        $arrUserPhones->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrUserPhones' => $arrUserPhones, 
            'contentPaginator' => $contentPaginator, 
            'userId' => $userId
        ]);
    }
    
    public function addAction(){
        $userId = (int)$this->params()->fromRoute('id', 0);
        $this->checkUserId($userId);
        $this->layout()->setTemplate('iframe/layout');
        $view = new ViewModel();
        $form = new AddForm();
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'user_id' => $userId,
                    'phone' => \Pxt\String\ChangeString::convertPhoneVn( $valuePost['phone'] ),
                    'status' => $valuePost['status'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entityUserPhones->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                return $this->redirect()->toRoute('admin/user-phones', ['action' => 'index', 'id' => $userId]);
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $view->setVariable('form', $form);
        $view->setVariable('userId', $userId);
        return $view;
    }
    
    public function editAction(){
        $userId = (int)$this->params()->fromRoute('id', 0);
        $this->checkUserId($userId);
        $this->layout()->setTemplate('iframe/layout');
        
        $view = new ViewModel();
        
        $request = $this->getRequest();
        $userPhoneId = (int)$this->params()->fromRoute('user_phones_id', 0);
        $valueCurrent = $this->entityUserPhones->fetchRow($userPhoneId);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('admin/user-phones');
        }else{
            $valuePost = $valueCurrent;
        }
        $form = new EditForm();
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'user_id' => $userId,
                    'phone' => \Pxt\String\ChangeString::convertPhoneVn( $valuePost['phone'] ),
                    'status' => $valuePost['status']
                ];
                $this->entityUserPhones->updateRow($userPhoneId, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $form->setData($valuePost);
        
        $view->setVariable('form', $form);
        $view->setVariable('userId', $userId);
        return $view;
    }
    
    public function checkUserId($id = 0){
        $valueCurrent = $this->entityUsers->fetchRow($id);
        if(empty($valueCurrent)){
            die('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết1!');
        }
        return true;
    }
    
    public function deleteAction(){
        $userId = (int)$this->params()->fromRoute('id', 0);
        $this->checkUserId($userId);
        
        $this->layout()->setTemplate('empty/layout');
        
        $request = $this->getRequest();
        $userPhoneId = (int)$this->params()->fromRoute('user_phones_id', 0);
        $valueCurrent = $this->entityUserPhones->fetchRow($userPhoneId);
        if(empty($valueCurrent)){
            $this->flashMessenger()->addWarningMessage('Lỗi dữ liệu, đề nghị liên hệ Admin!');
            die('success');
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];
        if($request->isPost()){
            // delete prizes
            $this->entityUserPhones->updateRow($userPhoneId, ['status' => '-1']);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form, 
            'checkRelationship' => $checkRelationship, 
            'valueCurrent' => $valueCurrent
        ]);
    }
    
}
