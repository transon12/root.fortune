<?php

namespace Companies\Controller;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Companies\Form\MissionDetails\AddForm;
use Companies\Form\MissionDetails\AddMissionsForm;
use Companies\Form\MissionDetails\CheckForm;
use Companies\Form\MissionDetails\DeleteForm;
use Companies\Form\MissionDetails\EditForm;
use Companies\Form\MissionDetails\UnCheckForm;
use Companies\Model\MissionDetails;
use Companies\Model\Missions;
use Settings\Model\Settings;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class MissionDetailsController extends AdminCore{

    public $entityCompanies;
    public $entitySettings;
    public $entityAddresses;
    public $entityMissionDetails;
    public $entityMissions;
    public $entityUsers;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Missions $entityMissions, MissionDetails $entityMissionDetails, Users $entityUsers){
        parent::__construct($entityPxtAuthentication); 
        $this->entitySettings = $entitySettings;
        $this->entityMissions = $entityMissions;
        $this->entityMissionDetails = $entityMissionDetails;
        $this->entityUsers = $entityUsers;
    }

    public function indexAction(){
        $arrUserId = $this->entityUsers->fetchRowAsId($this->sessionContainer->id);
        if($this->sessionContainer->id != '1' && ($arrUserId['company_id'] != "" || $arrUserId['company_id'] != null)){
            $this->redirect()->toRoute('admin/index', ['action' => 'index']);
        }
        $this->layout()->setTemplate('iframe/layout');
        $orderId = $this->params()->fromRoute('id', 0);
        $userName = $arrUserId['username'];

        $currentTime = \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent();

        $arrMissionDetails = $this->entityMissionDetails->fetchAll($orderId);
        //$arrMissionDetails = new Paginator(new ArrayAdapter( $this->entityMissionDetails->fetchAll($orderId)));
        //\Zend\Debug\Debug::dump($arrMissionDetails); die();
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // get setting paginator
        // $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        
        // // set per page
        // $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        // $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        // $arrMissionDetails->setCurrentPageNumber($page);
        // $arrMissionDetails->setItemCountPerPage($perPage);
        // $arrMissionDetails->setPageRange($contentPaginator['page_range']);
        //\Zend\Debug\Debug::dump($arrMissionDetails->setPageRange($contentPaginator['page_range'])); die();
        return new ViewModel([
            'orderId' => $orderId,
            'arrMissionDetails' => $arrMissionDetails,
            //'contentPaginator' => $contentPaginator,
            'optionMissions' => $this->entityMissions->fetchAll(),
            'userName' => $userName,
            'currentTime' => $currentTime,
            'optionUsers' => $this->entityUsers->fetchUserAsCompanies(),
        ]);
    }
    
    public function addAction(){
        $arrUserId = $this->entityUsers->fetchRowAsId($this->sessionContainer->id);
        if($this->sessionContainer->id != '1' && ($arrUserId['company_id'] != "" || $arrUserId['company_id'] != null)){
            $this->redirect()->toRoute('admin/index', ['action' => 'index']);
        }
        $this->layout()->setTemplate('iframe/layout');
        $orderId = $this->params()->fromRoute('id', 0);
        $request = $this->getRequest();
        $form = new AddForm($request->getRequestUri());

        $optionMissions = $this->entityMissions->fetchAll();
        $form->get('mission_id')->setValueOptions(['' => '--- Chọn một nhiệm vụ ---'] + $optionMissions);

        $optionUsers = $this->entityUsers->fetchUserAsCompanies();
        $form->get('user')->setValueOptions($optionUsers);

        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
           // \Zend\Debug\Debug::dump($valuePost); die();
            $form->setData($valuePost);
            if($form->isValid()){
                $valuePost['begined_at'] = (isset($valuePost['begined_at'])) ? $valuePost['begined_at'] : null;
                $valuePost['expected_at'] = (isset($valuePost['expected_at'])) ? $valuePost['expected_at'] : null;
                $beginAt = date_create_from_format('d/m/Y H:i:s', $valuePost['begined_at']);
                $expectAt = date_create_from_format('d/m/Y H:i:s', $valuePost['expected_at']);
                //echo $beginAt; die();
                $data = [
                    'mission_id' => $valuePost['mission_id'],
                    'order_id' => $orderId,
                    'user' => implode(", ",$valuePost['user']),
                    'begined_at' => (empty($beginAt))? null : date_format($beginAt, 'Y-m-d H:i:s'),
                    'expected_at' => (empty($expectAt))? null :  date_format($expectAt, 'Y-m-d H:i:s'),
                    'status' => '1',
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entityMissionDetails->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                //die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        return new ViewModel([
            'form' => $form,
            'orderId' => $orderId,
            'optionMissions' => $optionMissions,
            'optionUsers' => $optionUsers,
        ]);
    }

    public function editAction(){
        $arrUserId = $this->entityUsers->fetchRowAsId($this->sessionContainer->id);
        if($this->sessionContainer->id != '1' && ($arrUserId['company_id'] != "" || $arrUserId['company_id'] != null)){
            $this->redirect()->toRoute('admin/index', ['action' => 'index']);
        }
        $this->layout()->setTemplate('iframe/layout');
        $orderId = $this->params()->fromRoute('id', 0);
        $id = $this->params()->fromRoute('detailid', 0);
        $valueCurrent = $this->entityMissionDetails->fetchRow($orderId,$id);
        //\Zend\Debug\Debug::dump($valueCurrent); die();
        if(empty($valueCurrent)){
           $this->redirect()->toRoute('companies/mission-details',['action' => 'index', 'id' => $orderId]);
        }else{
            $valuePost = $valueCurrent;
            $valuePost['begined_at'] = (isset($valuePost['begined_at'])) ? date('d/m/Y H:i:s',strtotime($valuePost['begined_at'])) : '';
            $valuePost['expected_at'] = (isset($valuePost['expected_at'])) ? date('d/m/Y H:i:s',strtotime($valuePost['expected_at'])) : '';
            $valuePost['user'] = explode(", ",$valuePost['user'] );
            // \Zend\Debug\Debug::dump($valuePost['user']) ; die();
        }
        $request = $this->getRequest();
        $form = new EditForm($request->getRequestUri());

        $optionMissions = $this->entityMissions->fetchAll();
        $form->get('mission_id')->setValueOptions(['' => '--- Chọn một nhiệm vụ ---'] + $optionMissions);

        $optionUsers = $this->entityUsers->fetchUserAsCompanies();
        $form->get('user')->setValueOptions(['' => '--- Chọn tài khoản ---'] + $optionUsers);
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $valuePost['begined_at'] = (isset($valuePost['begined_at'])) ? $valuePost['begined_at'] : null;
                $valuePost['expected_at'] = (isset($valuePost['expected_at'])) ? $valuePost['expected_at'] : null;
                $beginAt = date_create_from_format('d/m/Y H:i:s', $valuePost['begined_at']);
                $expectAt = date_create_from_format('d/m/Y H:i:s', $valuePost['expected_at']);
                $data = [
                    'mission_id' => $valuePost['mission_id'],
                    'order_id' => $orderId,
                    'user' => implode(", ",$valuePost['user']),
                    'begined_at' => (empty($beginAt))? null : date_format($beginAt, 'Y-m-d H:i:s'),
                    'expected_at' => (empty($expectAt))? null :  date_format($expectAt, 'Y-m-d H:i:s'),
                    'status' => '1',
                ];
                $this->entityMissionDetails->updateRow($data,['order_id'=>$orderId, 'id' => $id]);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $form->setData($valuePost);
        return new ViewModel([
            'form' => $form,
            'orderId' => $orderId
        ]);
    }

    public function deleteAction(){
        $arrUserId = $this->entityUsers->fetchRowAsId($this->sessionContainer->id);
        if($this->sessionContainer->id != '1' && ($arrUserId['company_id'] != "" || $arrUserId['company_id'] != null)){
            $this->redirect()->toRoute('admin/index', ['action' => 'index']);
        }
        $this->layout()->setTemplate('empty/layout');
        $orderId = $this->params()->fromRoute('id', 0);
        $id = $this->params()->fromRoute('detailid', 0);
        $valueCurrent = $this->entityMissionDetails->fetchRow($orderId,$id);
        // \Zend\Debug\Debug::dump($valueCurrent) ; die();
        if(empty($valueCurrent)){
            die('success');
        }
        
        $request = $this->getRequest();
        $form = new DeleteForm($request->getRequestUri());
        if($request->isPost()){
            $this->entityMissionDetails->updateRow(['status'=> '-1'],['order_id' => $orderId, 'id' => $id]);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        return new ViewModel([
            'form' => $form,
            'valueCurrent' => $valueCurrent
        ]);
    }

    public function checkAction(){
        $arrUserId = $this->entityUsers->fetchRowAsId($this->sessionContainer->id);
        if($this->sessionContainer->id != '1' && ($arrUserId['company_id'] != "" || $arrUserId['company_id'] != null)){
            $this->redirect()->toRoute('admin/index', ['action' => 'index']);
        }
        $this->layout()->setTemplate('empty/layout');
        $orderId = $this->params()->fromRoute('id', 0);
        $id = $this->params()->fromRoute('detailid', 0);
        $valueCurrent = $this->entityMissionDetails->fetchRow($orderId,$id);
        //\Zend\Debug\Debug::dump($valueCurrent) ; die();
        if(empty($valueCurrent)){
            die('success');
        }

        $request = $this->getRequest();
        $form = new CheckForm($request->getRequestUri());

        if($request->isPost()){
            $endedAt = isset($valueCurrent['ended_at']) ? $valueCurrent['ended_at'] : \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent();
            if($endedAt <= $valueCurrent['expected_at']){
                $data = [
                    'status' => 2,
                    'ended_at' => $endedAt,
                ];
            }else{
                $data = [
                    'status' => 3,
                    'ended_at' => $endedAt,
                ];
            }
            $this->entityMissionDetails->updateRow($data,['order_id' => $orderId, 'id' => $id]);
            $this->flashMessenger()->addSuccessMessage('Đã check!');
            die('success');
        }
        return new ViewModel([
            'form' => $form,
            'valueCurrent' => $valueCurrent
        ]);
    }

    public function unCheckAction(){
        $arrUserId = $this->entityUsers->fetchRowAsId($this->sessionContainer->id);
        if($this->sessionContainer->id != '1' && ($arrUserId['company_id'] != "" || $arrUserId['company_id'] != null)){
            $this->redirect()->toRoute('admin/index', ['action' => 'index']);
        }
        $this->layout()->setTemplate('empty/layout');
        $orderId = $this->params()->fromRoute('id', 0);
        $id = $this->params()->fromRoute('detailid', 0);
        $valueCurrent = $this->entityMissionDetails->fetchRow($orderId,$id);
        if(empty($valueCurrent)){
            die('success');
        }

        $request = $this->getRequest();
        $form = new UnCheckForm($request->getRequestUri());

        if($request->isPost()){
            $data = [
                'status' => 1,
                'ended_at' => null,
            ];
            $this->entityMissionDetails->updateRow($data,['order_id' => $orderId, 'id' => $id]);
            $this->flashMessenger()->addSuccessMessage('Đã hủy check!');
            die('success');
        }
        return new ViewModel([
            'form' => $form,
            'valueCurrent' => $valueCurrent
        ]);
    }

    public function addMissionsAction(){
        $arrUserId = $this->entityUsers->fetchRowAsId($this->sessionContainer->id);
        if($this->sessionContainer->id != '1' && ($arrUserId['company_id'] != "" || $arrUserId['company_id'] != null)){
            $this->redirect()->toRoute('admin/index', ['action' => 'index']);
        }
        $this->layout()->setTemplate('empty/layout');
        $orderId = $this->params()->fromRoute('id', 0);
        $request = $this->getRequest();
        $form = new AddMissionsForm($request->getRequestUri());
       
        if($request->isPost()){
            $data = [
                'mission_name' => 'Xuất kẽm',
                'order_id' => $orderId,
                'user' => '',
                'begined_at' => '',
                'expected_at' => '',
                'status' => '1',
                'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
            ];
            $this->entityMissionDetails->addRow($data);
            $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
            die('success');
        }
        return new ViewModel([
            'form' => $form,
        ]);
    }
}