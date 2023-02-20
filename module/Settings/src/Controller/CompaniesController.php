<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Settings\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Settings\Model\Companies;
use Settings\Form\Companies\AddForm;
use Settings\Form\Companies\EditForm;
use Settings\Form\Companies\DeleteForm;
use Settings\Form\Companies\SearchForm;
use Settings\Model\Settings;
use Settings\Model\Cities;
use Settings\Model\Districts;
use Settings\Model\Wards;

class CompaniesController extends AdminCore{
    
    public $entityCompanies;
    public $entitySettings;
    public $entityCities;
    public $entityDistricts;
    public $entityWards;
    public $entityUsers;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, 
        Companies $entityCompanies, Cities $entityCities, Districts $entityDistricts, Wards $entityWards,
        Users $entityUsers) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityCompanies = $entityCompanies;
        $this->entityCities = $entityCities;
        $this->entityDistricts = $entityDistricts;
        $this->entityWards = $entityWards;
        $this->entityUsers = $entityUsers;
    }
    
    public function indexAction(){
        $userAsCompanyId = $this->entityUsers->fetchRowAsId($this->sessionContainer->id);
        //echo $userAsCompanyId['company_id']; die();
        if($this->sessionContainer->id != '1' && ($userAsCompanyId['company_id'] != "" || $userAsCompanyId['company_id'] != null)){
            $this->flashMessenger()->addWarningMessage('Bạn không đủ quyền vào đây!');
            $this->redirect()->toRoute('admin/index', ['action' => 'index']);
        }
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        
        $arrCompanies = new Paginator(new ArrayAdapter( $this->entityCompanies->fetchAll(['id' => $this->sessionContainer->id ]+$queries) ));
        //echo $this->sessionContainer->id; die();
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrCompanies->setCurrentPageNumber($page);
        $arrCompanies->setItemCountPerPage($perPage);
        $arrCompanies->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrCompanies' => $arrCompanies, 
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'userId' => $this->sessionContainer->id,
            'optionUsernames' => $this->entityUsers->fetchAllOptions() 
        ]);
    }
    
    public function addAction(){
        $userAsCompanyId = $this->entityUsers->fetchRowAsId($this->sessionContainer->id);
        if($this->sessionContainer->id != '1' && ($userAsCompanyId['company_id'] != "" || $userAsCompanyId['company_id'] != null)){
            $this->flashMessenger()->addWarningMessage('Bạn không đủ quyền vào đây!');
            $this->redirect()->toRoute('admin/index', ['action' => 'index']);
        }
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        //echo $request->getRequestUri(); die();
        $form = new AddForm($request->getRequestUri(),$this->sessionContainer->id);
        //add data user
        if($this->sessionContainer->id == '1'){
            $optionUsers = $this->entityUsers->fetchUserName();
            $form->get('user_id')->setValueOptions( ['' => '--- Chọn một tài khoản ---' ] + $optionUsers );
        }
        
        // add data cities
        $optionCities = $this->entityCities->fetchAllOptions(['country_id' => 'VN']);
        $form->get('city_id')->setValueOptions( ['' => '--- Chọn một tỉnh (thành phố) ---' ] + $optionCities );
        // add data default districts, wards
        $form->get('district_id')->setValueOptions( ['' => '--- Chọn một quận (huyện) ---' ] );
        $form->get('ward_id')->setValueOptions( ['' => '--- Chọn một phường (xã) ---' ] );
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
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
            $isValid = $form->isValid();
            $checkCompany = $this->entityCompanies->fetchRow($valuePost['id']);
            if(!empty($checkCompany)){
                $form->get('id')->setMessages($form->get('id')->getMessages() + ['id_exist' => 'Mã công ty đã tồn tại!']);
                $isValid = false;
            }
            
            if($isValid){
                $data = [
                    'id' => strtoupper( $valuePost['id'] ),
                    'name' => $valuePost['name'],
                    'tax_code' => $valuePost['tax_code'],
                    'address' => $valuePost['address'],
                    'ward_id' => $valuePost['ward_id'],
                    'phone' => $valuePost['phone'],
                    'status' => $valuePost['status'],
                    'user_id' => ($this->sessionContainer->id == '1') ? ($valuePost['user_id']== ''? null : $valuePost['user_id']) : $this->sessionContainer->id,
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                // add row
                $this->entityCompanies->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        
        return new ViewModel([
            'form' => $form,
            'userId' => $this->sessionContainer->id
        ]);
    }
    
    public function editAction(){
        $userAsCompanyId = $this->entityUsers->fetchRowAsId($this->sessionContainer->id);
        if($this->sessionContainer->id != '1'  && ($userAsCompanyId['company_id'] != "" || $userAsCompanyId['company_id'] != null)){
            $this->flashMessenger()->addWarningMessage('Bạn không đủ quyền vào đây!');
            $this->redirect()->toRoute('admin/index', ['action' => 'index']);
        }
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = $this->params()->fromRoute('id', '');
        $valueCurrent = $this->entityCompanies->fetchRow($id);
        
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('settings/companies');
        }else{
            $valuePost = $valueCurrent; 
        }
        
        $form = new EditForm($request->getRequestUri(),$this->sessionContainer->id);
        //$form->get('id')->setAttribute('readonly', 'readonly');
        if($this->sessionContainer->id == '1'){
            $optionUsers = $this->entityUsers->fetchAllOptions();
            $form->get('user_id')->setValueOptions( ['' => '--- Chọn một tài khoản ---' ] + $optionUsers );
        }
        // add data cities
        $optionCities = $this->entityCities->fetchAllOptions(['country_id' => 'VN']);
        $form->get('city_id')->setValueOptions( ['' => '--- Chọn một tỉnh (thành phố) ---' ] + $optionCities );
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
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
                    'tax_code' => $valuePost['tax_code'],
                    'ward_id' => $valuePost['ward_id'],
                    'phone' => $valuePost['phone'],
                    'user_id' => ($this->sessionContainer->id == '1') ? ($valuePost['user_id']== ''? null : $valuePost['user_id']) : $this->sessionContainer->id,
                    'status' => $valuePost['status']
                ];
                $this->entityCompanies->updateRow($id, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
                //return $this->redirect()->toRoute('admin/users');
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
            }
        }
        $form->setData($valuePost);
        
        return new ViewModel([
            'form' => $form,
            'userId' => $this->sessionContainer->id
        ]);
    }
    
    public function deleteAction(){
        if($this->sessionContainer->id != '1'){
            $this->flashMessenger()->addWarningMessage('Bạn không đủ quyền vào đây!');
            die('success');
        }
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = $this->params()->fromRoute('id', '');
        $valueCurrent = $this->entityCompanies->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('companies/index');
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
         $checkRelationship = [];
//         $countPhones = $this->entityPhones->fetchAll(['agent_id' => $id]);
//         if($countPhones > 0){
//             $checkRelationship['phones'] = 1;
//         }

        if($request->isPost()){
            // delete agents_id in phones
//             $data = ['agents_id' => null];
//             $this->entityPhones->updateRows(['agent_id' => $id], $data);
            // delete agents
            $this->entityCompanies->updateRow($id, ['status' => '-1']);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form, 
            'checkRelationship' => $checkRelationship, 
            'valueCurrent' => $valueCurrent
        ]);
    }

    public function iframeAddressAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = $this->params()->fromRoute('id',0);
        $valueCurrent = $this->entityCompanies->fetchRow($id);
        if(empty($valueCurrent)){
            die('success');
        }
        return new ViewModel([
            'id' => $id
        ]);
    }

    public function iframeSurrogateAction(){
        $this->layout()->setTemplate('empty/layout');
        $id = $this->params()->fromRoute('id',0);
        $valueCurrent = $this->entityCompanies->fetchRow($id);
        if(empty($valueCurrent)){
            die('success');
        }
        return new ViewModel([
            'id' => $id
        ]);
    }
}
