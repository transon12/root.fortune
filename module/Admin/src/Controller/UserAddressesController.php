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
use Admin\Model\UserAddresses;
use Admin\Model\Users;
use Admin\Form\UserAddresses\AddForm;
use Settings\Model\Countries;
use Settings\Model\Cities;
use Settings\Model\Districts;
use Settings\Model\Wards;
use Admin\Form\UserAddresses\EditForm;
use Admin\Form\UserAddresses\DeleteForm;

class UserAddressesController extends AdminCore{
    public $entityUserAddresses;
    public $entitySettings;
    public $entityUsers;
    public $entityCountries;
    public $entityCities;
    public $entityDistricts;
    public $entityWards;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, UserAddresses $entityUserAddresses, Users $entityUsers, 
        Countries $entityCountries, Cities $entityCities, Districts $entityDistricts, Wards $entityWards) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityUserAddresses = $entityUserAddresses;
        $this->entityUsers = $entityUsers;
        $this->entityCountries = $entityCountries;
        $this->entityCities = $entityCities;
        $this->entityDistricts = $entityDistricts;
        $this->entityWards = $entityWards;
    }
    
    public function indexAction(){
        $userId = (int)$this->params()->fromRoute('id', 0);
        $this->checkUserId($userId);
        $this->layout()->setTemplate('iframe/layout');
        $arrUserAddresses = new Paginator(new ArrayAdapter( $this->entityUserAddresses->fetchAlls(['user_id' => $userId]) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrUserAddresses->setCurrentPageNumber($page);
        $arrUserAddresses->setItemCountPerPage($perPage);
        $arrUserAddresses->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrUserAddresses' => $arrUserAddresses, 
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
        // add data countries
        $optionCountries = $this->entityCountries->fetchAllOptions();
        $form->get('country_id')->setValueOptions( ['' => '--- Ch???n m???t ?????t n?????c ---' ] + $optionCountries );
        // add data default cities, districts, wards
        $form->get('city_id')->setValueOptions( ['' => '--- Ch???n m???t t???nh (th??nh ph???) ---' ] );
        $form->get('district_id')->setValueOptions( ['' => '--- Ch???n m???t qu???n (huy???n) ---' ] );
        $form->get('ward_id')->setValueOptions( ['' => '--- Ch???n m???t ph?????ng (x??) ---' ] );
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            // load data cities
            if(isset($valuePost['country_id']) && $valuePost['country_id'] != ''){
                $optionCities = $this->entityCities->fetchAllOptions(['country_id' => $valuePost['country_id']]);
                $form->get('city_id')->setValueOptions( ['0' => '--- Ch???n m???t t???nh (th??nh ph???) ---' ] + $optionCities );
            }
            // load data districts
            if(isset($valuePost['city_id']) && $valuePost['city_id'] != ''){
                $optionDistricts = $this->entityDistricts->fetchAllOptions(['city_id' => $valuePost['city_id']]);
                $form->get('district_id')->setValueOptions( ['0' => '--- Ch???n m???t qu???n (huy???n) ---' ] + $optionDistricts );
            }
            // load data wards
            if(isset($valuePost['district_id']) && $valuePost['district_id'] != ''){
                $optionWards = $this->entityWards->fetchAllOptions(['district_id' => $valuePost['district_id']]);
                $form->get('ward_id')->setValueOptions( ['0' => '--- Ch???n m???t ph?????ng (x??) ---' ] + $optionWards );
            }
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'user_id' => $userId,
                    'address' => $valuePost['address'],
                    'phone' => $valuePost['phone'],
                    'ward_id' => $valuePost['ward_id'],
                    'status' => $valuePost['status'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entityUserAddresses->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Th??m d??? li???u th??nh c??ng!');
                return $this->redirect()->toRoute('admin/user-addresses', ['action' => 'index', 'id' => $userId]);
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
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
        $userAddressId = (int)$this->params()->fromRoute('user_addresses_id', 0);
        $valueCurrent = $this->entityUserAddresses->fetchRow($userAddressId);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('admin/user-addresses');
        }else{
            $valuePost = $valueCurrent;
        }
        $form = new EditForm();
        $optionCountries = $this->entityCountries->fetchAllOptions();
        $form->get('country_id')->setValueOptions( ['' => '--- Ch???n m???t ?????t n?????c ---' ] + $optionCountries );
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            // load data cities
            if(isset($valuePost['country_id']) && $valuePost['country_id'] != ''){
                $optionCities = $this->entityCities->fetchAllOptions(['country_id' => $valuePost['country_id']]);
                $form->get('city_id')->setValueOptions( ['0' => '--- Ch???n m???t t???nh (th??nh ph???) ---' ] + $optionCities );
            }
            // load data districts
            if(isset($valuePost['city_id']) && $valuePost['city_id'] != ''){
                $optionDistricts = $this->entityDistricts->fetchAllOptions(['city_id' => $valuePost['city_id']]);
                $form->get('district_id')->setValueOptions( ['0' => '--- Ch???n m???t qu???n (huy???n) ---' ] + $optionDistricts );
            }
            // load data wards
            if(isset($valuePost['district_id']) && $valuePost['district_id'] != ''){
                $optionWards = $this->entityWards->fetchAllOptions(['district_id' => $valuePost['district_id']]);
                $form->get('ward_id')->setValueOptions( ['0' => '--- Ch???n m???t ph?????ng (x??) ---' ] + $optionWards );
            }
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'user_id' => $userId,
                    'address' => $valuePost['address'],
                    'phone' => $valuePost['phone'],
                    'ward_id' => $valuePost['ward_id'],
                    'status' => $valuePost['status']
                ];
                $this->entityUserAddresses->updateRow($userAddressId, $data);
                $this->flashMessenger()->addSuccessMessage('S???a d??? li???u th??nh c??ng!');
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }else{
            if(isset($valuePost['ward_id']) && $valuePost['ward_id'] != ''){
                $ward = $this->entityWards->fetchRow($valuePost['ward_id']);
                if(isset($ward['district_id']) && $ward['district_id'] != '' && $ward['district_id'] != null){
                    $optionWards = $this->entityWards->fetchAllOptions(['district_id' => $ward['district_id']]);
                    $form->get('ward_id')->setValueOptions( ['0' => '--- Ch???n m???t ph?????ng (x??) ---' ] + $optionWards );
                    $district = $this->entityDistricts->fetchRow($ward['district_id']);
                    $valuePost['district_id'] = $ward['district_id'];
                }
                if(isset($district['city_id']) && $district['city_id'] != '' && $district['city_id'] != null){
                    $optionDistricts = $this->entityDistricts->fetchAllOptions(['city_id' => $district['city_id']]);
                    $form->get('district_id')->setValueOptions( ['0' => '--- Ch???n m???t qu???n (huy???n) ---' ] + $optionDistricts );
                    $city = $this->entityCities->fetchRow($district['city_id']);
                    $valuePost['city_id'] = $district['city_id'];
                }
                if(isset($city['country_id']) && $city['country_id'] != '' && $city['country_id'] != null){
                    $optionCities = $this->entityCities->fetchAllOptions(['country_id' => $city['country_id']]);
                    $form->get('city_id')->setValueOptions( ['0' => '--- Ch???n m???t t???nh (th??nh ph???) ---' ] + $optionCities );
                    $country = $this->entityCountries->fetchRow($city['country_id']);
                    $valuePost['country_id'] = $city['country_id'];
                }
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
            die('C?? l???i trong qu?? tr??nh x??? l??! Li??n h??? Admin ????? bi???t th??m chi ti???t1!');
        }
        return true;
    }
    
    public function deleteAction(){
        $userId = (int)$this->params()->fromRoute('id', 0);
        $this->checkUserId($userId);
        
        $this->layout()->setTemplate('empty/layout');
        
        $request = $this->getRequest();
        $userAddressId = (int)$this->params()->fromRoute('user_addresses_id', 0);
        $valueCurrent = $this->entityUserAddresses->fetchRow($userAddressId);
        if(empty($valueCurrent)){
            $this->flashMessenger()->addWarningMessage('L???i d??? li???u, ????? ngh??? li??n h??? Admin!');
            die('success');
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];
        if($request->isPost()){
            // delete prizes
            $this->entityUserAddresses->updateRow($userAddressId, ['status' => '-1']);
            $this->flashMessenger()->addSuccessMessage('X??a d??? li???u th??nh c??ng!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form, 
            'checkRelationship' => $checkRelationship, 
            'valueCurrent' => $valueCurrent
        ]);
    }
    
}
