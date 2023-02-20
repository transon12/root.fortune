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
use Settings\Model\Companies;
use Settings\Model\Settings;
use Settings\Model\CompanyConfigs;
use Settings\Form\CompanyConfigs\PaginatorsForm;
use Settings\Form\CompanyConfigs\MessagesForm;
use Settings\Form\CompanyConfigs\SuppliesForm;
use Settings\Form\CompanyConfigs\DisplaysForm;
use Zend\Filter\File\RenameUpload;
use Settings\Form\CompanyConfigs\FormForm;
use Settings\Form\CompanyConfigs\FormDeleteForm;
use Settings\Form\CompanyConfigs\ManageForm;
use Settings\Form\CompanyConfigs\ManageDeleteForm;
use Settings\Form\CompanyConfigs\ConfigCodesForm;
use Settings\Form\CompanyConfigs\ConfigCodesDeleteForm;
use Settings\Form\CompanyConfigs\LayoutsForm;
use Settings\Form\CompanyConfigs\LayoutsDeleteForm;

class CompanyConfigsController extends AdminCore{
    
    public $entityCompanies;
    public $entitySettings;
    public $entityCompanyConfigs;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, 
        Companies $entityCompanies, CompanyConfigs $entityCompanyConfigs) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityCompanies = $entityCompanies;
        $this->entityCompanyConfigs = $entityCompanyConfigs;
    }
    
    public function paginatorsAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);
        
        $companyConfigId = 'paginators';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);
        
        $valuePost = [];
        if(!empty($arrCompanyConfig)){
            $valuePost = json_decode($arrCompanyConfig['content'], true);
        }else{
            $valuePost = [
                'per_page' => '',
                'per_pages' => '',
                'page_range' => ''
            ];
            $this->entityCompanyConfigs->addRow(['id' => $companyConfigId, 'company_id' => $companyId, 'content' => json_encode($valuePost)]);
        }
        //\Zend\Debug\Debug::dump($valuePost); die();
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        //echo $request->getRequestUri(); die();
        $form = new PaginatorsForm($request->getRequestUri());
        $form->setData($valuePost);
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'per_page' => $valuePost['per_page'],
                    'per_pages' => $valuePost['per_pages'],
                    'page_range' => $valuePost['page_range']
                ];
                // add row
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($data)]);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                //die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        
        return new ViewModel(['form' => $form]);
    }
    
    public function messagesAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'messages';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);
        //\Zend\Debug\Debug::dump($arrCompanyConfig); die();
        $valuePost = [];
        if(!empty($arrCompanyConfig)){
            $valuePost = json_decode($arrCompanyConfig['content'], true);
            //\Zend\Debug\Debug::dump($valuePost); die();
        }else{
            $valuePost = [
                'message_success'       => '',
                'message_invalid'       => '',
                'message_checked'       => '',
                'message_outdate'       => '',
                'message_checked_limit' => '',
                'message_success_qrcode' => '',
                'message_checked_qrcode' => '',
            ];
            $this->entityCompanyConfigs->addRow(['id' => $companyConfigId, 'company_id' => $companyId, 'content' => json_encode($valuePost)]);
        }
        //\Zend\Debug\Debug::dump($valuePost); die();
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        //echo $request->getRequestUri(); die();
        $form = new MessagesForm($request->getRequestUri());
        $form->setData($valuePost);
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'message_success'       => $valuePost['message_success'],
                    'message_invalid'       => $valuePost['message_invalid'],
                    'message_checked'       => $valuePost['message_checked'],
                    'message_outdate'       => $valuePost['message_outdate'],
                    'message_checked_limit' => $valuePost['message_checked_limit'],
                    'message_success_qrcode' => $valuePost['message_success_qrcode'],
                    'message_checked_qrcode' => $valuePost['message_checked_qrcode'],
                    'message_checked_same_phone' => $valuePost['message_checked_same_phone'],
                ];
                // add row
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($data)]);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                //die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        
        return new ViewModel(['form' => $form]);
    }
    
    public function displaysAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);
        $companyConfigId = 'displays';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);
        $valuePost = [];
        $content = json_decode($arrCompanyConfig['content'], true);
        if(!empty($arrCompanyConfig)){
            $valuePost = $content;
        }else{
            $valuePost = [
                'logo' => '',
                'style' => ''
            ];
            $this->entityCompanyConfigs->addRow(['id' => $companyConfigId, 'company_id' => $companyId, 'content' => json_encode($valuePost)]);
        }
        //\Zend\Debug\Debug::dump($valuePost); die();
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        //echo $request->getRequestUri(); die();
        $form = new DisplaysForm($request->getRequestUri());
        $form->setData($valuePost);

        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $files = $request->getFiles()->toArray();
            //\Zend\Debug\Debug::dump($valuePost);
            //\Zend\Debug\Debug::dump($files);
            //die();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if($form->isValid()){
                $data = [];
                if($files['logo']['name'] != ''){
                    $data['logo'] = $this->uploadFile($files, 'logo', $companyId);
                }else{
                    $data['logo'] = $content['logo'];
                }
                $valuePost['logo'] = $data['logo'];
                $data['style'] = $valuePost['style'];
                // add row
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($data)]);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                //die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        
        //\Zend\Debug\Debug::dump($valuePost); die();
        return new ViewModel([
            'form' => $form,
            'valuePost' => $valuePost
        ]);
    }
    
    private function uploadFile($file = null, $param = null, $companyId = ''){
        if($file == null || $param == null) return '';
        $info = pathinfo($file[$param]['name']);
        $url = 'logos/' . strtolower($companyId) . '-logo-' . time() . '.' . $info['extension'];
        $fileUpload = new RenameUpload([
            'target' => FILES_UPLOAD . $url,
            'randomize' => false
        ]);
        $fileUpload->filter($file[$param]);
        return $url;
    }
    
    public function suppliesAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'supplies';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);
        
        $valuePost = [];
        if(!empty($arrCompanyConfig)){
            $valuePost = json_decode($arrCompanyConfig['content'], true);
        }else{
            $valuePost = [
                'time_limit_supplies_ins' => '',
                'time_limit_supplies_outs' => '',
                'time_limit_proposal_details' => ''
            ];
            $this->entityCompanyConfigs->addRow(['id' => $companyConfigId, 'company_id' => $companyId, 'content' => json_encode($valuePost)]);
        }
        //\Zend\Debug\Debug::dump($valuePost); die();
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        //echo $request->getRequestUri(); die();
        $form = new SuppliesForm($request->getRequestUri());
        $form->setData($valuePost);
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'time_limit_supplies_ins' => $valuePost['time_limit_supplies_ins'],
                    'time_limit_supplies_outs' => $valuePost['time_limit_supplies_outs'],
                    'time_limit_proposal_details' => $valuePost['time_limit_proposal_details']
                ];
                // add row
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($data)]);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                //die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        
        return new ViewModel(['form' => $form]);
    }

    /**
     * form_products
     */
    public function formProductsIframeAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'form_products';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);
        
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        
        $view->setVariable('id', $companyId);
        return $view;
    }
    public function formProductsAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'form_products';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $this->layout()->setTemplate('iframe/layout');
        
        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }else{
            $this->entityCompanyConfigs->addRow(['id' => $companyConfigId, 'company_id' => $companyId, 'content' => json_encode($contents)]);
        }
        
        return new ViewModel([
            'contents' => $contents, 
            'companyId' => $companyId, 
            'companyConfigId' => $companyConfigId
        ]);
    }
    public function formProductsAddAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'form_products';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }
        
        $view = new ViewModel();
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new FormForm($request->getRequestUri());
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if( isset($contents[$valuePost['key']]) ) {
                $form->get('key')->setMessages($form->get('key')->getMessages() + ['key_exist' => 'Thuộc tính này đã tồn tại trước đó!']);
                $isValid = false;
            }
            if($isValid){
                $contents[$valuePost['key']] = [
                    'name' => $valuePost['name'],
                    'type' => $valuePost['type']
                ];
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }

        $view->setVariable('form', $form);
        return $view;
    }
    public function formProductsEditAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'form_products';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }
        
        $view = new ViewModel();
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();

        $contentKey = $this->params()->fromRoute('content_key', '');
        if(!isset($contents[$contentKey])){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('success');
        }
        
        $form = new FormForm($request->getRequestUri());
        $form->get('key')->setAttribute('readonly', 'readonly');
        $valuePost = $contents[$contentKey];
        $valuePost['key'] = $contentKey;
        $form->setData($valuePost);
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if($isValid){
                $contents[$valuePost['key']] = [
                    'name' => $valuePost['name'],
                    'type' => $valuePost['type']
                ];
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }

        $view->setVariable('form', $form);
        return $view;
    }
    public function formProductsDeleteAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'form_products';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }

        $contentKey = $this->params()->fromRoute('content_key', '');
        if(!isset($contents[$contentKey])){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('success');
        }
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new FormDeleteForm($request->getRequestUri());
        
        if($request->isPost()){
            unset($contents[$contentKey]);
            $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form
        ]);
    }
    /**
     * form_products
     */
    
    /**
     * form_agents
     */
    public function formAgentsIframeAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'form_agents';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);
        
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        
        $view->setVariable('id', $companyId);
        return $view;
    }
    
    public function formAgentsAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'form_agents';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $this->layout()->setTemplate('iframe/layout');
        
        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }else{
            $this->entityCompanyConfigs->addRow(['id' => $companyConfigId, 'company_id' => $companyId, 'content' => json_encode($contents)]);
        }
        
        return new ViewModel([
            'contents' => $contents, 
            'companyId' => $companyId, 
            'companyConfigId' => $companyConfigId
        ]);
    }
    
    public function formAgentsAddAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'form_agents';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }
        
        $view = new ViewModel();
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new FormForm($request->getRequestUri());
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if( isset($contents[$valuePost['key']]) ) {
                $form->get('key')->setMessages($form->get('key')->getMessages() + ['key_exist' => 'Thuộc tính này đã tồn tại trước đó!']);
                $isValid = false;
            }
            if($isValid){
                $contents[$valuePost['key']] = [
                    'name' => $valuePost['name'],
                    'type' => $valuePost['type']
                ];
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }

        $view->setVariable('form', $form);
        return $view;
    }
    
    public function formAgentsEditAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'form_agents';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }
        
        $view = new ViewModel();
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();

        $contentKey = $this->params()->fromRoute('content_key', '');
        if(!isset($contents[$contentKey])){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('success');
        }
        
        $form = new FormForm($request->getRequestUri());
        $form->get('key')->setAttribute('readonly', 'readonly');
        $valuePost = $contents[$contentKey];
        $valuePost['key'] = $contentKey;
        $form->setData($valuePost);
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if($isValid){
                $contents[$valuePost['key']] = [
                    'name' => $valuePost['name'],
                    'type' => $valuePost['type']
                ];
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }

        $view->setVariable('form', $form);
        return $view;
    }
    
    public function formAgentsDeleteAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'form_agents';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }

        $contentKey = $this->params()->fromRoute('content_key', '');
        if(!isset($contents[$contentKey])){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('success');
        }
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new FormDeleteForm($request->getRequestUri());
        
        if($request->isPost()){
            unset($contents[$contentKey]);
            $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form
        ]);
    }
    /**
     * form_agents
     */

    /**
     * manage_products
     */
    public function manageProductsIframeAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'manage_products';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);
        
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        
        $view->setVariable('id', $companyId);
        return $view;
    }
    public function manageProductsAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'manage_products';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $this->layout()->setTemplate('iframe/layout');
        
        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }else{
            $this->entityCompanyConfigs->addRow(['id' => $companyConfigId, 'company_id' => $companyId, 'content' => json_encode($contents)]);
        }
        
        return new ViewModel([
            'contents' => $contents, 
            'companyId' => $companyId, 
            'companyConfigId' => $companyConfigId
        ]);
    }
    public function manageProductsAddAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'manage_products';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }
        
        $view = new ViewModel();
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new ManageForm($request->getRequestUri());
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if( isset($contents[$valuePost['key']]) ) {
                $form->get('key')->setMessages($form->get('key')->getMessages() + ['key_exist' => 'Thuộc tính này đã tồn tại trước đó!']);
                $isValid = false;
            }
            if($isValid){
                $contents[$valuePost['key']] = [
                    'name' => $valuePost['name'],
                    'type' => $valuePost['type'],
                    'status' => $valuePost['status']
                ];
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }

        $view->setVariable('form', $form);
        return $view;
    }
    public function manageProductsEditAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'manage_products';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }
        
        $view = new ViewModel();
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();

        $contentKey = $this->params()->fromRoute('content_key', '');
        if(!isset($contents[$contentKey])){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('success');
        }
        
        $form = new ManageForm($request->getRequestUri());
        $form->get('key')->setAttribute('readonly', 'readonly');
        $valuePost = $contents[$contentKey];
        $valuePost['key'] = $contentKey;
        $form->setData($valuePost);
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if($isValid){
                $contents[$valuePost['key']] = [
                    'name' => $valuePost['name'],
                    'type' => $valuePost['type'],
                    'status' => $valuePost['status']
                ];
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }

        $view->setVariable('form', $form);
        return $view;
    }
    public function manageProductsDeleteAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'manage_products';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }

        $contentKey = $this->params()->fromRoute('content_key', '');
        if(!isset($contents[$contentKey])){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('success');
        }
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new ManageDeleteForm($request->getRequestUri());
        
        if($request->isPost()){
            unset($contents[$contentKey]);
            $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form
        ]);
    }
    /**
     * manage_products
     */

    /**
     * manage_agents
     */
    public function manageAgentsIframeAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'manage_agents';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);
        
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        
        $view->setVariable('id', $companyId);
        return $view;
    }
    public function manageAgentsAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'manage_agents';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $this->layout()->setTemplate('iframe/layout');
        
        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }else{
            $this->entityCompanyConfigs->addRow(['id' => $companyConfigId, 'company_id' => $companyId, 'content' => json_encode($contents)]);
        }
        
        return new ViewModel([
            'contents' => $contents, 
            'companyId' => $companyId, 
            'companyConfigId' => $companyConfigId
        ]);
    }
    public function manageAgentsAddAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'manage_agents';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }
        
        $view = new ViewModel();
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new ManageForm($request->getRequestUri());
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if( isset($contents[$valuePost['key']]) ) {
                $form->get('key')->setMessages($form->get('key')->getMessages() + ['key_exist' => 'Thuộc tính này đã tồn tại trước đó!']);
                $isValid = false;
            }
            if($isValid){
                $contents[$valuePost['key']] = [
                    'name' => $valuePost['name'],
                    'type' => $valuePost['type'],
                    'status' => $valuePost['status']
                ];
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }

        $view->setVariable('form', $form);
        return $view;
    }
    public function manageAgentsEditAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'manage_agents';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }
        
        $view = new ViewModel();
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();

        $contentKey = $this->params()->fromRoute('content_key', '');
        if(!isset($contents[$contentKey])){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('success');
        }
        
        $form = new ManageForm($request->getRequestUri());
        $form->get('key')->setAttribute('readonly', 'readonly');
        $valuePost = $contents[$contentKey];
        $valuePost['key'] = $contentKey;
        $form->setData($valuePost);
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if($isValid){
                $contents[$valuePost['key']] = [
                    'name' => $valuePost['name'],
                    'type' => $valuePost['type'],
                    'status' => $valuePost['status']
                ];
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }

        $view->setVariable('form', $form);
        return $view;
    }
    public function manageAgentsDeleteAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'manage_agents';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }

        $contentKey = $this->params()->fromRoute('content_key', '');
        if(!isset($contents[$contentKey])){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('success');
        }
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new ManageDeleteForm($request->getRequestUri());
        
        if($request->isPost()){
            unset($contents[$contentKey]);
            $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form
        ]);
    }
    /**
     * manage_agents
     */

    /**
     * config_codes
     */
    public function configCodesIframeAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'config_codes';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);
        
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        
        $view->setVariable('id', $companyId);
        return $view;
    }
    public function configCodesAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'config_codes';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $this->layout()->setTemplate('iframe/layout');
        
        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }else{
            $this->entityCompanyConfigs->addRow(['id' => $companyConfigId, 'company_id' => $companyId, 'content' => json_encode($contents)]);
        }
        
        return new ViewModel([
            'contents' => $contents, 
            'companyId' => $companyId, 
            'companyConfigId' => $companyConfigId
        ]);
    }
    public function configCodesAddAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'config_codes';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }
        
        $view = new ViewModel();
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new ConfigCodesForm($request->getRequestUri());
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if( isset($contents[$valuePost['key']]) ) {
                $form->get('key')->setMessages($form->get('key')->getMessages() + ['key_exist' => 'Thuộc tính này đã tồn tại trước đó!']);
                $isValid = false;
            }
            if($isValid){
                $contents[$valuePost['key']] = [
                    'name' => $valuePost['name'],
                    'prefix_code' => $valuePost['prefix_code'],
                    'prefix_serial' => $valuePost['prefix_serial'],
                    'serial_begin' => $valuePost['serial_begin']
                ];
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }

        $view->setVariable('form', $form);
        return $view;
    }
    public function configCodesEditAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'config_codes';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }
        
        $view = new ViewModel();
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();

        $contentKey = $this->params()->fromRoute('content_key', '');
        if(!isset($contents[$contentKey])){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('success');
        }
        
        $form = new ConfigCodesForm($request->getRequestUri());
        $form->get('key')->setAttribute('readonly', 'readonly');
        $valuePost = $contents[$contentKey];
        $valuePost['key'] = $contentKey;
        $form->setData($valuePost);
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if($isValid){
                $contents[$valuePost['key']] = [
                    'name' => $valuePost['name'],
                    'prefix_code' => $valuePost['prefix_code'],
                    'prefix_serial' => $valuePost['prefix_serial'],
                    'serial_begin' => $valuePost['serial_begin']
                ];
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }

        $view->setVariable('form', $form);
        return $view;
    }
    public function configCodesDeleteAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'config_codes';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }

        $contentKey = $this->params()->fromRoute('content_key', '');
        if(!isset($contents[$contentKey])){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('success');
        }
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new ConfigCodesDeleteForm($request->getRequestUri());
        
        if($request->isPost()){
            unset($contents[$contentKey]);
            $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form
        ]);
    }
    /**
     * config_codes
     */

    /**
     * layouts
     */
    public function layoutsIframeAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'layouts';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);
        
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        
        $view->setVariable('id', $companyId);
        return $view;
    }
    public function layoutsAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'layouts';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $this->layout()->setTemplate('iframe/layout');
        
        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }else{
            $this->entityCompanyConfigs->addRow(['id' => $companyConfigId, 'company_id' => $companyId, 'content' => json_encode($contents)]);
        }
        
        return new ViewModel([
            'contents' => $contents, 
            'companyId' => $companyId, 
            'companyConfigId' => $companyConfigId
        ]);
    }
    public function layoutsAddAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'layouts';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }
        
        $view = new ViewModel();

        $this->layout()->setTemplate('iframe/layout');
        $request = $this->getRequest();
        
        $form = new LayoutsForm($request->getRequestUri());
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if( isset($contents[$valuePost['key']]) ) {
                $form->get('key')->setMessages($form->get('key')->getMessages() + ['key_exist' => 'Thuộc tính này đã tồn tại trước đó!']);
                $isValid = false;
            }
           // \Zend\Debug\Debug::dump($form->getMessages()); die();
            if($isValid){
                $contents[$valuePost['key']] = [
                    'layout' => $valuePost['layout'],
                    'content' => $valuePost['content']
                ];
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                $this->redirect()->toRoute('settings/company-configs', ['action' => 'layouts', 'id' => $companyId]);
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }

        $view->setVariable('form', $form);
        $view->setVariable('companyId', $companyId);
        return $view;
    }
    public function layoutsEditAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'layouts';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }
        
        $view = new ViewModel();

        $this->layout()->setTemplate('iframe/layout');
        $request = $this->getRequest();

        $contentKey = $this->params()->fromRoute('content_key', '');
        if(!isset($contents[$contentKey])){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            $this->redirect()->toRoute('settings/company-configs', ['action' => 'layouts', 'id' => $companyId]);
        }
        
        $form = new LayoutsForm($request->getRequestUri());
        $form->get('key')->setAttribute('readonly', 'readonly');
        $valuePost = $contents[$contentKey];
        $valuePost['key'] = $contentKey;
        $form->setData($valuePost);
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if($isValid){
                $contents[$valuePost['key']] = [
                    'layout' => $valuePost['layout'],
                    'content' => $valuePost['content']
                ];
                $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }

        $view->setVariable('form', $form);
        $view->setVariable('companyId', $companyId);
        return $view;
    }
    public function layoutsDeleteAction(){

        $companyId = $this->params()->fromRoute('id', '');
        $arrCompany = $this->checkCompanyId($companyId);

        $companyConfigId = 'layouts';
        $arrCompanyConfig = $this->entityCompanyConfigs->fetchRow(['id' => $companyConfigId, 'company_id' => $companyId]);

        $contents = [];
        if(!empty($arrCompanyConfig)){
            $contents = json_decode($arrCompanyConfig['content'], true);
        }

        $contentKey = $this->params()->fromRoute('content_key', '');
        if(!isset($contents[$contentKey])){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('success');
        }
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $form = new LayoutsDeleteForm($request->getRequestUri());
        
        if($request->isPost()){
            unset($contents[$contentKey]);
            $this->entityCompanyConfigs->updateRow($companyConfigId, $companyId, ['content' => json_encode($contents)]);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form
        ]);
    }
    /**
     * layouts
     */
    
    
    public function checkCompanyId($id = ''){
        if($this->sessionContainer->id != '1'){
            $this->flashMessenger()->addWarningMessage('Bạn không đủ quyền vào đây!');
            die('success');
        }
        $valueCurrent = $this->entityCompanies->fetchRow($id);
        if(empty($valueCurrent)){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('success');
        }
        return $valueCurrent;
    }
}
