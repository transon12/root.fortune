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
use Settings\Model\Countries;
use Settings\Model\Cities;
use Settings\Model\Districts;
use Admin\Model\UserIdentityCards;
use Admin\Form\UserIdentityCards\AddForm;
use Admin\Form\UserIdentityCards\EditForm;
use Admin\Form\UserIdentityCards\DeleteForm;

class UserIdentityCardsController extends AdminCore{
    public $entityUserIdentityCards;
    public $entitySettings;
    public $entityUsers;
    public $entityCountries;
    public $entityCities;
    public $entityDistricts;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, UserIdentityCards $entityUserIdentityCards, 
        Users $entityUsers, Countries $entityCountries, Cities $entityCities, Districts $entityDistricts) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityUserIdentityCards = $entityUserIdentityCards;
        $this->entityUsers = $entityUsers;
        $this->entityCountries = $entityCountries;
        $this->entityCities = $entityCities;
        $this->entityDistricts = $entityDistricts;
    }
    
    public function indexAction(){
        $userId = (int)$this->params()->fromRoute('id', 0);
        $this->checkUserId($userId);
        $this->layout()->setTemplate('iframe/layout');
        $arrUserIdentityCards = new Paginator(new ArrayAdapter( $this->entityUserIdentityCards->fetchAlls(['user_id' => $userId]) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrUserIdentityCards->setCurrentPageNumber($page);
        $arrUserIdentityCards->setItemCountPerPage($perPage);
        $arrUserIdentityCards->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrUserIdentityCards' => $arrUserIdentityCards, 
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
            $form->setData($valuePost);
            if($form->isValid()){
                $dateOfIssues = date_create_from_format('d/m/Y', $valuePost['date_of_issues']);
                $data = [
                    'user_id' => $userId,
                    'code' => $valuePost['code'],
                    'date_of_issues' => date_format($dateOfIssues, 'Y-m-d'),
                    'note' => $valuePost['note'],
                    'district_id' => $valuePost['district_id'],
                    'status' => $valuePost['status'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                //\Zend\Debug\Debug::dump($data); die();
                $this->entityUserIdentityCards->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Th??m d??? li???u th??nh c??ng!');
                return $this->redirect()->toRoute('admin/user-identity-cards', ['action' => 'index', 'id' => $userId]);
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
        $userIdentityCardId = (int)$this->params()->fromRoute('user_identity_cards_id', 0);
        $valueCurrent = $this->entityUserIdentityCards->fetchRow($userIdentityCardId);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('admin/user-identity-cards');
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
            $form->setData($valuePost);
            if($form->isValid()){
                $dateOfIssues = date_create_from_format('d/m/Y', $valuePost['date_of_issues']);
                $data = [
                    'code' => $valuePost['code'],
                    'date_of_issues' => date_format($dateOfIssues, 'Y-m-d'),
                    'note' => $valuePost['note'],
                    'district_id' => $valuePost['district_id'],
                    'status' => $valuePost['status']
                ];
                $this->entityUserIdentityCards->updateRow($userIdentityCardId, $data);
                $this->flashMessenger()->addSuccessMessage('S???a d??? li???u th??nh c??ng!');
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }else{
            if(isset($valuePost['district_id']) && $valuePost['district_id'] != ''){
                $district = $this->entityDistricts->fetchRow($valuePost['district_id']);
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
            $valuePost['date_of_issues'] = date_format(date_create($valuePost['date_of_issues']), 'd/m/Y');
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
        $userIdentityCardId = (int)$this->params()->fromRoute('user_identity_cards_id', 0);
        $valueCurrent = $this->entityUserIdentityCards->fetchRow($userIdentityCardId);
        if(empty($valueCurrent)){
            $this->flashMessenger()->addWarningMessage('L???i d??? li???u, ????? ngh??? li??n h??? Admin!');
            die('success');
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];
        if($request->isPost()){
            // delete prizes
            $this->entityUserIdentityCards->updateRow($userIdentityCardId, ['status' => '-1']);
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
