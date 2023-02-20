<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Supplies\Controller;

use Zend\View\Model\ViewModel;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Settings\Model\Settings;
use Supplies\Model\Suppliers;
use Supplies\Form\Suppliers\SearchForm;
use Supplies\Form\Suppliers\AddForm;
use Settings\Model\Countries;
use Settings\Model\Cities;
use Settings\Model\Districts;
use Settings\Model\Wards;
use Supplies\Form\Suppliers\EditForm;
use Supplies\Form\Suppliers\DeleteForm;
use Settings\Model\Companies;

class SuppliersController extends AdminCore{
    
    public $entitySuppliers;
    public $entitySettings;
    public $entityCountries;
    public $entityCities;
    public $entityDistricts;
    public $entityWards;
    public $entityCompanies;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, Suppliers $entitySuppliers, 
        Countries $entityCountries, Cities $entityCities, Districts $entityDistricts, Wards $entityWards, Companies $entityCompanies) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entitySuppliers = $entitySuppliers;
        $this->entityCountries = $entityCountries;
        $this->entityCities = $entityCities;
        $this->entityDistricts = $entityDistricts;
        $this->entityWards = $entityWards;
        $this->entityCompanies = $entityCompanies;
    }
    
    public function indexAction(){
        $formSearch = new SearchForm('index', $this->sessionContainer->id);
        $optionCompanies = $this->entityCompanies->fetchAllToOptions();
        if($this->sessionContainer->id == '1'){
            $formSearch->get('company_id')->setValueOptions( [
                '' => '--- Chọn một công ty ---'] + 
                $optionCompanies 
            );
        }
        $queries = $this->params()->fromQuery();
        // reset company_id if empty
        if(!isset($queries['company_id']) || $queries['company_id'] == ''){
            $queries['company_id'] = isset($queries['company_id']) ? $queries['company_id'] : $this->defineCompanyId;
        }
        $formSearch->setData($queries);
        
        $arrSuppliers = new Paginator(new ArrayAdapter( $this->entitySuppliers->fetchAlls($queries) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrSuppliers->setCurrentPageNumber($page);
        $arrSuppliers->setItemCountPerPage($perPage);
        $arrSuppliers->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrSuppliers' => $arrSuppliers, 
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'userId' => $this->sessionContainer->id,
            'optionCompanies' => $optionCompanies
        ]);
    }
    
    public function addAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        
        $request = $this->getRequest();
        $form = new AddForm($request->getRequestUri());
        // add data countries
        $optionCountries = $this->entityCountries->fetchAllOptions();
        $form->get('country_id')->setValueOptions( ['' => '--- Chọn một đất nước ---' ] + $optionCountries );
        // add data default cities, districts, wards
        $form->get('city_id')->setValueOptions( ['' => '--- Chọn một tỉnh (thành phố) ---' ] );
        $form->get('district_id')->setValueOptions( ['' => '--- Chọn một quận (huyện) ---' ] );
        $form->get('ward_id')->setValueOptions( ['' => '--- Chọn một phường (xã) ---' ] );
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            // load data cities
            if(isset($valuePost['country_id']) && $valuePost['country_id'] != ''){
                $optionCities = $this->entityCities->fetchAllOptions(['country_id' => $valuePost['country_id']]);
                $form->get('city_id')->setValueOptions( ['0' => '--- Chọn một tỉnh (thành phố) ---' ] + $optionCities );
            }
            // load data districts
            if(isset($valuePost['city_id']) && $valuePost['city_id'] != ''){
                $optionDistricts = $this->entityDistricts->fetchAllOptions(['city_id' => $valuePost['city_id']]);
                $form->get('district_id')->setValueOptions( ['0' => '--- Chọn một quận (huyện) ---' ] + $optionDistricts );
            }
            // load data wards
            if(isset($valuePost['district_id']) && $valuePost['district_id'] != ''){
                $optionWards = $this->entityWards->fetchAllOptions(['district_id' => $valuePost['district_id']]);
                $form->get('ward_id')->setValueOptions( ['0' => '--- Chọn một phường (xã) ---' ] + $optionWards );
            }
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'company_id' => $this->defineCompanyId,
                    'name' => $valuePost['name'],
                    'address' => $valuePost['address'],
                    'phone' => $valuePost['phone'],
                    'ward_id' => $valuePost['ward_id'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                //\Zend\Debug\Debug::dump($data); die();
                $this->entitySuppliers->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $view->setVariable('form', $form);
        return $view;
    }
    
    public function editAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entitySuppliers->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('supplies/suppliers');
        }else{
            $valuePost = $valueCurrent; 
        }
        
        $form = new EditForm($request->getRequestUri());
        $optionCountries = $this->entityCountries->fetchAllOptions();
        $form->get('country_id')->setValueOptions( ['' => '--- Chọn một đất nước ---' ] + $optionCountries );
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            // load data cities
            if(isset($valuePost['country_id']) && $valuePost['country_id'] != ''){
                $optionCities = $this->entityCities->fetchAllOptions(['country_id' => $valuePost['country_id']]);
                $form->get('city_id')->setValueOptions( ['0' => '--- Chọn một tỉnh (thành phố) ---' ] + $optionCities );
            }
            // load data districts
            if(isset($valuePost['city_id']) && $valuePost['city_id'] != ''){
                $optionDistricts = $this->entityDistricts->fetchAllOptions(['city_id' => $valuePost['city_id']]);
                $form->get('district_id')->setValueOptions( ['0' => '--- Chọn một quận (huyện) ---' ] + $optionDistricts );
            }
            // load data wards
            if(isset($valuePost['district_id']) && $valuePost['district_id'] != ''){
                $optionWards = $this->entityWards->fetchAllOptions(['district_id' => $valuePost['district_id']]);
                $form->get('ward_id')->setValueOptions( ['0' => '--- Chọn một phường (xã) ---' ] + $optionWards );
            }
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'name' => $valuePost['name'],
                    'address' => $valuePost['address'],
                    'phone' => $valuePost['phone'],
                    'ward_id' => $valuePost['ward_id']
                ];
                $this->entitySuppliers->updateRow($id, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }else{
            if(isset($valuePost['ward_id']) && $valuePost['ward_id'] != ''){
                $ward = $this->entityWards->fetchRow($valuePost['ward_id']);
                if(isset($ward['district_id']) && $ward['district_id'] != '' && $ward['district_id'] != null){
                    $optionWards = $this->entityWards->fetchAllOptions(['district_id' => $ward['district_id']]);
                    $form->get('ward_id')->setValueOptions( ['0' => '--- Chọn một phường (xã) ---' ] + $optionWards );
                    $district = $this->entityDistricts->fetchRow($ward['district_id']);
                    $valuePost['district_id'] = $ward['district_id'];
                }
                if(isset($district['city_id']) && $district['city_id'] != '' && $district['city_id'] != null){
                    $optionDistricts = $this->entityDistricts->fetchAllOptions(['city_id' => $district['city_id']]);
                    $form->get('district_id')->setValueOptions( ['0' => '--- Chọn một quận (huyện) ---' ] + $optionDistricts );
                    $city = $this->entityCities->fetchRow($district['city_id']);
                    $valuePost['city_id'] = $district['city_id'];
                }
                if(isset($city['country_id']) && $city['country_id'] != '' && $city['country_id'] != null){
                    $optionCities = $this->entityCities->fetchAllOptions(['country_id' => $city['country_id']]);
                    $form->get('city_id')->setValueOptions( ['0' => '--- Chọn một tỉnh (thành phố) ---' ] + $optionCities );
                    $country = $this->entityCountries->fetchRow($city['country_id']);
                    $valuePost['country_id'] = $city['country_id'];
                }
            }
        }
        $form->setData($valuePost);
        
        return new ViewModel(['form' => $form]);
    }
    
    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entitySuppliers->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('supplies/suppliers');
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];
        
        if($request->isPost()){
            $this->entitySuppliers->updateRow($id, ['status' => '-1']);
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
