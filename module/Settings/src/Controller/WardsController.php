<?php

namespace Settings\Controller;

use Zend\View\Model\ViewModel;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Settings\Model\Districts;
use Settings\Model\Wards;

class WardsController extends AdminCore{

    public $entityWards;
    public $entityDistricts;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Wards $entityWards, Districts $entityDistricts) {
        parent::__construct($entityPxtAuthentication);
        $this->entityWards = $entityWards;
        $this->entityDistricts = $entityDistricts;
    }
    
    public function indexAction(){
        die();
    }
    
    public function getWardsAsDistrictsAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        $id = '0';
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $id = isset($valuePost['id']) ? $valuePost['id'] : '0';
            $valueCurrent = $this->entityDistricts->fetchRow($id);
            if(empty($valueCurrent)){
                die('error');
            }
        }else{
            die('error');
        }
        
        $optionsData = $this->entityWards->fetchAllOptions(['district_id' => $id]);
        
        return new ViewModel(['optionsData' => $optionsData]);
    }
}
