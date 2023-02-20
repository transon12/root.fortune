<?php

namespace Persons\Controller;

use Admin\Core\AdminCore;
use Admin\Model\Positions;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Persons\Model\LeaveLists;
use Persons\Model\Profiles;
use Persons\Model\TimeKeeping;
use Settings\Model\Settings;
use Zend\Filter\File\RenameUpload;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class TimeKeepingController extends AdminCore{

    public $entitySettings;
    public $entityUsers;
    public $entityProfiles;
    public $entityLeaveLists;
    public $entityPositions;
    public $entityTimeKeeping;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Users $entityUsers, Profiles $entityProfiles, TimeKeeping $entityTimeKeeping){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityUsers = $entityUsers;
        $this->entityProfiles = $entityProfiles;
        $this->entityTimeKeeping = $entityTimeKeeping;
    }

    public function indexAction(){
        return new ViewModel();
    }

    public function viewAction(){
        $id = $this->params()->fromRoute("id",0);
        // echo $id; die();
        $request = $this->getRequest();
        // $valuePost = $request->getPost()->toArray();
        $userId = $this->sessionContainer->id;
        if($request->isPost()){
            $arrTimeKeeping = $this->entityTimeKeeping->fetchAllByUserId($userId);
            if(!empty($arrTimeKeeping)){
                foreach($arrTimeKeeping as $item){
                    $data[] = [
                        'title'   => $item["title"],
                        'start'   => $item["timekeeping"],
                    ];
                }
                // \Zend\Debug\Debug::dump($data);
                echo json_encode($data);
            }else{
                echo json_encode([]);
            }
            die();
        }else{
            die();
        }
        
    }
}