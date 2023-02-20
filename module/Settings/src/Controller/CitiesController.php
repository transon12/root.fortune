<?php

namespace Settings\Controller;

use Zend\View\Model\ViewModel;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Settings\Model\Cities;
use Settings\Model\Countries;

class CitiesController extends AdminCore{

    public $entityCities;
    public $entityCountries;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Cities $entityCities, Countries $entityCountries) {
        parent::__construct($entityPxtAuthentication);
        $this->entityCities = $entityCities;
        $this->entityCountries = $entityCountries;
    }
    
    public function indexAction(){
        die();
    }
    
    public function getCitiesAsCountriesAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        $id = '0';
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $id = isset($valuePost['id']) ? $valuePost['id'] : '0';
            $valueCurrent = $this->entityCountries->fetchRow($id);
            if(empty($valueCurrent)){
                die('error');
            }
        }else{
            die('error');
        }
        
        $optionsData = $this->entityCities->fetchAllOptions(['country_id' => $id]);
        //\Zend\Debug\Debug::dump($optionsData); die();
        
        return new ViewModel(['optionsData' => $optionsData]);
    }
}
