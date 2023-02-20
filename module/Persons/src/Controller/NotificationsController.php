<?php

namespace Persons\Controller;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Persons\Model\Notifications;
use Persons\Model\Profiles;
use Settings\Model\Settings;
use Zend\View\Model\ViewModel;

class NotificationsController extends AdminCore{
    
    public $entitySettings;
    public $entityUserKpis;
    public $entityUsers;
    public $entityProfiles;
    public $entityNotifications;
    

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Users $entityUsers, Profiles $entityProfiles, Notifications $entityNotifications){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityUsers = $entityUsers;
        $this->entityProfiles = $entityProfiles;
        $this->entityNotifications = $entityNotifications;
    }

    public function indexAction(){
        $this->layout()->setTemplate('empty/layout');
        $userId = \Admin\Service\Authentication::getId();
        $limit = 10;
        $request =  $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            if(isset($valuePost['page'])){
                if($valuePost['page'] != null && $valuePost['page'] != ''){
                    $page = (int)$this->params()->fromPost('page',0);
                    $rowBegin = ($page - 1)* $limit;
                    // $countNotify = (int)$this->entityNotifications->countByUserId($userId);
                    $arrNotifications = $this->entityNotifications->fetchAllByUserId($userId, $rowBegin, $limit);
                    // \Zend\Debug\Debug::dump($optionUserName); die();
                    if(($page !=1) && empty($arrNotifications)){
                        $this->flashMessenger()->addWarningMessage('Đã hết thông báo!');
                        return false;
                    }
                }else{
                    die();
                }
            }else{
                die();
            }
        }else{
            $this->flashMessenger()->addWarningMessage('Không đủ quyền truy cập, đề nghị liên hệ Admin để biết thêm chi tiết!');
		    $this->redirect()->toRoute('admin/index');
        }
        return new ViewModel([
            'arrNotifications' => $arrNotifications,
            // 'countNotify' => $countNotify,
            'page' => $page,
            'optionUser' => $this->entityUsers->fetchOptions(),
        ]);
    }

    public function markReadAction(){
        $userId = $this->sessionContainer->id;
        $request =  $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            if(empty($valuePost)){
                die("abc");
            }
            if(isset($valuePost['notifyId']) && !empty($valuePost['notifyId'])){
                $strNotifyId = "'" . implode("', '", $valuePost['notifyId']) . "'";
                $this->entityNotifications->updateRow(["is_read" => '1'], $userId, $strNotifyId);
                // \Zend\Debug\Debug::dump($strNotifyId); die();
                die('success');
            }
        }else{
            $this->flashMessenger()->addWarningMessage('Không đủ quyền truy cập, đề nghị liên hệ Admin để biết thêm chi tiết!');
		    $this->redirect()->toRoute('admin/index');
        }
        // return new ViewModel();
    }

    public function notifyUnreadAction(){
        $userId = $this->sessionContainer->id;
        $countNotifyUnread = (int)$this->entityNotifications->countByUserId($userId);
        if($countNotifyUnread == 0){
            die();
        }
        die("$countNotifyUnread");
    }
}