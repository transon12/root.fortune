<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Storehouses\Controller;

use Zend\View\Model\ViewModel;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Settings\Model\Settings;
use Settings\Model\Countries;
use Settings\Model\Cities;
use Settings\Model\Districts;
use Settings\Model\Wards;
use Storehouses\Model\Storehouses;
use Storehouses\Form\Index\SearchForm;
use Storehouses\Form\Index\AddForm;
use Storehouses\Form\Index\EditForm;
use Storehouses\Form\Index\DeleteForm;
use Settings\Model\Companies;

class IndexController extends AdminCore{
    
    public $entityStorehouses;
    public $entitySettings;
    public $entityCountries;
    public $entityCities;
    public $entityDistricts;
    public $entityWards;
    public $entityCompanies;
    public $entityUsers;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, Storehouses $entityStorehouses, 
        Countries $entityCountries, Cities $entityCities, Districts $entityDistricts, Wards $entityWards, Companies $entityCompanies,
        Users $entityUsers) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityStorehouses = $entityStorehouses;
        $this->entityCountries = $entityCountries;
        $this->entityCities = $entityCities;
        $this->entityDistricts = $entityDistricts;
        $this->entityWards = $entityWards;
        $this->entityCompanies = $entityCompanies;
        $this->entityUsers = $entityUsers;
    }
    
    public function indexAction(){
        $formSearch = new SearchForm('index', $this->sessionContainer->id);
        if($this->sessionContainer->id == '1'){
            $arrCompanies = $this->entityCompanies->fetchAllToOptions();
            $formSearch->get('company_id')->setValueOptions( [
                '' => '--- Chọn một công ty ---'] + 
                $arrCompanies 
            );
        }
        $optionUsers = $this->entityUsers->fetchAllOptions(["company_id" => $this->defineCompanyId]);
        $formSearch->get('user_id')->setValueOptions( ['' => '--- Chọn một tài khoản tạo ---' ] + $optionUsers );
        $queries = $this->params()->fromQuery();
        // reset company_id if empty
        if(!isset($queries['company_id']) || $queries['company_id'] == ''){
            $queries['company_id'] = isset($queries['company_id']) ? $queries['company_id'] : $this->defineCompanyId;
        }
        $formSearch->setData($queries);
        
        $arrStorehouses = new Paginator(new ArrayAdapter( $this->entityStorehouses->fetchAlls($queries) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrStorehouses->setCurrentPageNumber($page);
        $arrStorehouses->setItemCountPerPage($perPage);
        $arrStorehouses->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrStorehouses' => $arrStorehouses, 
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'userConfigs' => $this->sessionContainer->configs,
            'userId' => $this->sessionContainer->id,
            'optionCompanies' => $this->entityCompanies->fetchAllToOptions(),
            'optionUsers' => $optionUsers
        ]);
    }
    
    public function addAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        
        $request = $this->getRequest();
        $form = new AddForm($request->getRequestUri());
        // add data countries
        // $optionCountries = $this->entityCountries->fetchAllOptions();
        // $form->get('country_id')->setValueOptions( ['' => '--- Chọn một đất nước ---' ] + $optionCountries );
        // // add data default cities, districts, wards
        // $form->get('city_id')->setValueOptions( ['' => '--- Chọn một tỉnh (thành phố) ---' ] );
        // $form->get('district_id')->setValueOptions( ['' => '--- Chọn một quận (huyện) ---' ] );
        // $form->get('ward_id')->setValueOptions( ['' => '--- Chọn một phường (xã) ---' ] );
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            // load data cities
            // if(isset($valuePost['country_id']) && $valuePost['country_id'] != ''){
            //     $optionCities = $this->entityCities->fetchAllOptions(['country_id' => $valuePost['country_id']]);
            //     $form->get('city_id')->setValueOptions( ['0' => '--- Chọn một tỉnh (thành phố) ---' ] + $optionCities );
            // }
            // // load data districts
            // if(isset($valuePost['city_id']) && $valuePost['city_id'] != ''){
            //     $optionDistricts = $this->entityDistricts->fetchAllOptions(['city_id' => $valuePost['city_id']]);
            //     $form->get('district_id')->setValueOptions( ['0' => '--- Chọn một quận (huyện) ---' ] + $optionDistricts );
            // }
            // // load data wards
            // if(isset($valuePost['district_id']) && $valuePost['district_id'] != ''){
            //     $optionWards = $this->entityWards->fetchAllOptions(['district_id' => $valuePost['district_id']]);
            //     $form->get('ward_id')->setValueOptions( ['0' => '--- Chọn một phường (xã) ---' ] + $optionWards );
            // }
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'company_id' => $this->defineCompanyId,
                    'user_id' => $this->sessionContainer->id,
                    'name' => $valuePost['name'],
                    'address' => $valuePost['address'],
                    // 'ward_id' => $valuePost['ward_id'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                //\Zend\Debug\Debug::dump($data); die();
                $this->entityStorehouses->addRow($data);
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
        $valueCurrent = $this->entityStorehouses->fetchRow($id);
        if(empty($valueCurrent)){
            die("Không tìm thấy kho này. Vui lòng kiểm tra lại!");
        }else{
            $valuePost = $valueCurrent; 
        }
        
        $form = new EditForm($request->getRequestUri());
        // $optionCountries = $this->entityCountries->fetchAllOptions();
        // $form->get('country_id')->setValueOptions( ['' => '--- Chọn một đất nước ---' ] + $optionCountries );
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            // load data cities
            // if(isset($valuePost['country_id']) && $valuePost['country_id'] != ''){
            //     $optionCities = $this->entityCities->fetchAllOptions(['country_id' => $valuePost['country_id']]);
            //     $form->get('city_id')->setValueOptions( ['0' => '--- Chọn một tỉnh (thành phố) ---' ] + $optionCities );
            // }
            // // load data districts
            // if(isset($valuePost['city_id']) && $valuePost['city_id'] != ''){
            //     $optionDistricts = $this->entityDistricts->fetchAllOptions(['city_id' => $valuePost['city_id']]);
            //     $form->get('district_id')->setValueOptions( ['0' => '--- Chọn một quận (huyện) ---' ] + $optionDistricts );
            // }
            // // load data wards
            // if(isset($valuePost['district_id']) && $valuePost['district_id'] != ''){
            //     $optionWards = $this->entityWards->fetchAllOptions(['district_id' => $valuePost['district_id']]);
            //     $form->get('ward_id')->setValueOptions( ['0' => '--- Chọn một phường (xã) ---' ] + $optionWards );
            // }
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'name' => $valuePost['name'],
                    'address' => $valuePost['address'],
                    // 'ward_id' => $valuePost['ward_id']
                ];
                $this->entityStorehouses->updateRow($id, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }else{
            // if(isset($valuePost['ward_id']) && $valuePost['ward_id'] != ''){
            //     $valuePost['ward_id'] = $valuePost['ward_id'];
            //     $ward = $this->entityWards->fetchRow($valuePost['ward_id']);
            //     if(isset($ward['district_id']) && $ward['district_id'] != '' && $ward['district_id'] != null){
            //         $optionWards = $this->entityWards->fetchAllOptions(['district_id' => $ward['district_id']]);
            //         $form->get('ward_id')->setValueOptions( ['0' => '--- Chọn một phường (xã) ---' ] + $optionWards );
            //         $district = $this->entityDistricts->fetchRow($ward['district_id']);
            //         $valuePost['district_id'] = $ward['district_id'];
            //     }
            //     if(isset($district['city_id']) && $district['city_id'] != '' && $district['city_id'] != null){
            //         $optionDistricts = $this->entityDistricts->fetchAllOptions(['city_id' => $district['city_id']]);
            //         $form->get('district_id')->setValueOptions( ['0' => '--- Chọn một quận (huyện) ---' ] + $optionDistricts );
            //         $city = $this->entityCities->fetchRow($district['city_id']);
            //         $valuePost['city_id'] = $district['city_id'];
            //     }
            //     if(isset($city['country_id']) && $city['country_id'] != '' && $city['country_id'] != null){
            //         $optionCities = $this->entityCities->fetchAllOptions(['country_id' => $city['country_id']]);
            //         $form->get('city_id')->setValueOptions( ['0' => '--- Chọn một tỉnh (thành phố) ---' ] + $optionCities );
            //         $country = $this->entityCountries->fetchRow($city['country_id']);
            //         $valuePost['country_id'] = $city['country_id'];
            //     }
            // }
        }
        $form->setData($valuePost);
        //die('abcdef');
        return new ViewModel(['form' => $form]);
    }
    
    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityStorehouses->fetchRow($id);
        if(empty($valueCurrent)){
            die("Không tìm thấy kho này. Vui lòng kiểm tra lại!");
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];
        
        if($request->isPost()){
            $this->entityStorehouses->deleteRow($id);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form, 
            'checkRelationship' => $checkRelationship, 
            'valueCurrent' => $valueCurrent
        ]);
    }
    
    public function iframeSuppliesInsAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityStorehouses->fetchRow($id);
        //\Zend\Debug\Debug::dump($valueCurrent); die();
        if(empty($valueCurrent)){
            die('success');
        }
        $view->setVariable('id', $id);
        return $view;
    }
    
    public function iframeImportsAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityStorehouses->fetchRow($id);
        if(empty($valueCurrent)){
            die('success');
        }
        $view->setVariable('id', $id);
        return $view;
    }
}
