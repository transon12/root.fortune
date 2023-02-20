<?php

namespace Settings\Controller;

use Zend\View\Model\ViewModel;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Settings\Model\Cities;
use Settings\Model\Districts;

class DistrictsController extends AdminCore{

    public $entityDistricts;
    public $entityCities;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Districts $entityDistricts, Cities $entityCities) {
        parent::__construct($entityPxtAuthentication);
        $this->entityDistricts = $entityDistricts;
        $this->entityCities = $entityCities;
    }
    
    public function indexAction(){
        die();
    }
    
    public function getDistrictsAsCitiesAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        $id = '0';
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $id = isset($valuePost['id']) ? $valuePost['id'] : '0';
            $valueCurrent = $this->entityCities->fetchRow($id);
            if(empty($valueCurrent)){
                die('error');
            }
        }else{
            die('error');
        }
        
        $optionsData = $this->entityDistricts->fetchAllOptions(['city_id' => $id]);
        
        return new ViewModel(['optionsData' => $optionsData]);
    }
}
