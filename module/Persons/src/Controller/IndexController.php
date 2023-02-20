<?php

namespace Persons\Controller;

use Admin\Core\AdminCore;
use Admin\Model\Groups;
use Admin\Model\GroupsPositionsUsers;
use Admin\Model\PxtAuthentication;
use Persons\Form\Index\DeleteForm;
use Persons\Form\Index\EditForm;
use Persons\Form\Index\UploadFileForm;
use Persons\Model\Departments;
use Persons\Model\Notifications;
use Settings\Model\FileUploads;
use Settings\Model\Settings;
use Zend\Filter\File\RenameUpload;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class IndexController extends AdminCore{
    
    public $entitySettings;
    public $entityFileUploads;
    public $entityDepartments;
    public $entityGroups;
    public $entityGroupsPositionsUsers;
    public $entityNotifications;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    FileUploads $entityFileUploads, Groups $entityGroups, GroupsPositionsUsers $entityGroupsPositionsUsers,
    Notifications $entityNotifications){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityFileUploads = $entityFileUploads;
        $this->entityGroups = $entityGroups;
        $this->entityGroupsPositionsUsers = $entityGroupsPositionsUsers;
        $this->entityNotifications = $entityNotifications;
    }

    public function indexAction(){
        if($this->sessionContainer->id == 1){
            $arrGroups = $this->entityGroups->fetchAlls();  
        }else{
            $userGroupPostion = $this->entityGroupsPositionsUsers->fetchAllAsUsersId(['user_id' => $this->sessionContainer->id]);
            $i = 0;
            foreach($userGroupPostion as $item){
                $groupId[$i] = $item['group_id'];
                $i++;
            };
            $arrGroups = $this->entityGroups->fetchAllGroupByUserCurrent($groupId); 
        }
         
        return new ViewModel([
            'arrGroups' => $arrGroups,
        ]);
    }

    private function uploadFile($file = null,$path, $reName){
        $url = $path."/" . time() . '-' . $reName;
        $fileUpload = new RenameUpload([
            'target' => FILES_UPLOAD . $url,
            'randomize' => false
        ]);
        // \Zend\Debug\Debug::dump($file) ; die();
        $fileUpload->filter($file);
        return $url;
    }

    public function addAction(){
        $this->layout()->setTemplate('empty/layout');
        $userId = $this->sessionContainer->id;
        $request = $this->getRequest();
        $form = new UploadFileForm($request->getRequestUri());

        $optionGroups = $this->entityGroups->fetchOptionByName();
        $form->get('group_id')->setValueOptions( ['' => '--- Chọn nơi upload ---' ] + $optionGroups );
        if($request->isPost()){
            $valuePost = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $fetchRowGroup = $this->entityGroups->fetchRow($valuePost['group_id']);
            $folderPath = $fetchRowGroup['url_upload_file'];
            
            $arrUserId = $this->entityGroupsPositionsUsers->fetchAllUserIdByGroupsId($valuePost['group_id']); 
            $form->setData($valuePost);
            if($form->isValid()){
                $file = $valuePost["file"];
                $extension = strtolower( pathinfo($file["name"], PATHINFO_EXTENSION) );
                $reName = \Pxt\String\ChangeString::changeSlug($file["name"]);
                $data = [
                    "group_id" => $valuePost['group_id'],
                    "user_id"       => $userId,
                    "parent_id"     => 0,
                    "level"         => 0,
                    "url"           => $this->uploadFile( $file, $folderPath, $reName),
                    "type"          => $file["type"],
                    "name"          => (isset($valuePost["name"]) && $valuePost["name"] !='') ? $valuePost["name"] : $reName,
                    "extension"     => $extension,
                    "size"          => $file["size"],
                    "created_at"    => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                
                if(!empty($arrUserId)){
                    $arrDataNotify = [];
                    foreach($arrUserId as $key => $item){
                        $arrDataNotify[$key] = [
                            "user_id_send" => $userId,
                            "user_id_receive" => $item,
                            "description" => "Thông báo đến " . $optionGroups[$valuePost['group_id']],
                            "url" => "/persons/index/index/",
                            "created_at"    => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                        ];
                        $this->entityNotifications->addRow($arrDataNotify[$key]);
                    }
                }
                $this->entityFileUploads->addRow($data);
                // $this->uploadFile($file, $reName);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die("success");
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
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityFileUploads->fetchRowFileDept($id);
        
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('persons/index');
        }else{
            $valuePost = $valueCurrent;
        }
        //\Zend\Debug\Debug::dump($valuePost); die();
        $request = $this->getRequest();
        $form = new EditForm($request->getRequestUri());
        $form->setData($valuePost);
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $this->entityFileUploads->updateRow($id, ['name' => $valuePost['name']]);
            $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
            die("success");
        }
        
        return new ViewModel([
            'form' => $form,
            'id' => $id,
            'valueCurrent' => $valueCurrent
        ]);
    }

    public function seeAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityFileUploads->fetchRowFileDept($id);
        if(empty($valueCurrent)){
            return false;
            $this->redirect()->toRoute('persons/index');
        }
        if($this->sessionContainer->id != 1){
            $userGroupPostion = $this->entityGroupsPositionsUsers->fetchAllAsUsersId(['user_id' => $this->sessionContainer->id]);
            $i = 0;
            foreach($userGroupPostion as $item){
                $groupId[$i] = $item['group_id'];
                $i++;
            };
            if(in_array($valueCurrent['group_id'],$groupId)){
                $filePath = $valueCurrent['url'];
            }else{
                $this->flashMessenger()->addWarningMessage('Không có quyền xem tập tin này!');
                die("success");
            }
        }
        $filePath = $valueCurrent['url'];
        return new ViewModel([
            'filePath' => $filePath,
        ]);
    }

    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityFileUploads->fetchRowFileDept($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('persons/index');
        }
        $filePath = $valueCurrent['url'];
        //$file = FILES_UPLOAD. $filePath;
        $request = $this->getRequest();
        $form = new DeleteForm($request->getRequestUri());

        if($request->isPost()){
            //unlink($file); xóa file trong đường dẫn
            $this->entityFileUploads->deleteRow($id);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die("success");
        }
        return new ViewModel([
            'form' => $form,
            'id' => $id,
            'valueCurrent' => $valueCurrent
        ]);
    }
}
