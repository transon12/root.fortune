<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Settings\Controller;

use Zend\View\Model\ViewModel;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Settings\Form\Index\ConfigForm;
use Settings\Form\Index\PaginatorsDefaultForm;
use Settings\Form\Index\MessagesDefaultForm;
use Settings\Form\Index\SuppliesDefaultForm;
use Settings\Form\Index\MessagesForm;
use Settings\Model\Companies;
use Settings\Form\Index\ConfigsForm;
use Settings\Model\CompanyConfigs;

class IndexController extends AdminCore{
    public $entitySettings;
    public $entityCompanies;
    public $entityCompanyConfigs;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, Companies $entityCompanies,
        CompanyConfigs $entityCompanyConfigs) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityCompanies = $entityCompanies;
        $this->entityCompanyConfigs = $entityCompanyConfigs;
    }
    
    /**
     * id = config
     */
    public function indexAction(){
        $code = 'configs';
        $form = new ConfigForm();
        $arrCurrent = $this->entitySettings->fetchRow($code);
        if(empty($arrCurrent)){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý, đề nghị liên hệ quản trị viên để được xử lý trường hợp này!');
        }else{
            // Set value
            $content = json_decode($arrCurrent['content'], true);
            $form->setData($content);
            // Check request post
            $request = $this->getRequest();
            if($request->isPost()){
                $valuePost = $request->getPost()->toArray();
                $form->setData($valuePost);
                if($form->isValid()){
                    $dataArray = [
                        'cookie_lifetime' => $valuePost['cookie_lifetime'],
                        'remember_me' => $valuePost['remember_me'],
                        'allow_ips_connect_api_sms' => $valuePost['allow_ips_connect_api_sms']
                    ];
                    // data build to json
                    $data = [
                        'content' => json_encode($dataArray),
                    ];
                    $this->entitySettings->updateRow($code, $data);
                    $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                    return $this->redirect()->toRoute('settings/index', ['action' => 'index']);
                }else{
                    $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
                }
            }
        }
        return new ViewModel(['form' => $form]);
    }
    
    /**
     * id = messages
     */
    public function messagesDefaultAction(){
        $code = 'messages';
        $form = new MessagesDefaultForm();
        $arrCurrent = $this->entitySettings->fetchRow($code);
        if(empty($arrCurrent)){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý, đề nghị liên hệ quản trị viên để được xử lý trường hợp này!');
        }else{
            // Set value
            $content = json_decode($arrCurrent['content'], true);
            $form->setData($content);
            // Check request post
            $request = $this->getRequest();
            if($request->isPost()){
                $valuePost = $request->getPost()->toArray();
                $form->setData($valuePost);
                if($form->isValid()){
                    $dataArray = [
                        'message_success' => $valuePost['message_success'],
                        'message_invalid' => $valuePost['message_invalid'],
                        'message_checked' => $valuePost['message_checked'],
                        'message_outdate' => $valuePost['message_outdate'],
                        'message_success_agent' => $valuePost['message_success_agent'],
                        'message_invalid_agent' => $valuePost['message_invalid_agent'],
                        'message_checked_agent' => $valuePost['message_checked_agent'],
                        'message_outdate_agent' => $valuePost['message_outdate_agent']
                    ];
                    // data build to json
                    $data = [
                        'content' => json_encode($dataArray),
                    ];
                    $this->entitySettings->updateRow($code, $data);
                    $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                    return $this->redirect()->toRoute('settings/index', ['action' => 'messages-default']);
                }else{
                    $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
                }
            }
        }
        return new ViewModel(['form' => $form]);
    }
    
    /**
     * id = paginators
     */
    public function paginatorsDefaultAction(){
        $code = 'paginators';
        $form = new PaginatorsDefaultForm();
        $arrCurrent = $this->entitySettings->fetchRow($code);
        if(empty($arrCurrent)){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý, đề nghị liên hệ quản trị viên để được xử lý trường hợp này!');
        }else{
            // Set value
            $content = json_decode($arrCurrent['content'], true);
            $form->setData($content);
            // Check request post
            $request = $this->getRequest();
            if($request->isPost()){
                $valuePost = $request->getPost()->toArray();
                $form->setData($valuePost);
                if($form->isValid()){
                    $dataArray = [
                        'per_page' => $valuePost['per_page'],
                        'per_pages' => $valuePost['per_pages'],
                        'page_range' => $valuePost['page_range']
                    ];
                    // data build to json
                    $data = [
                        'content' => json_encode($dataArray),
                    ];
                    $this->entitySettings->updateRow($code, $data);
                    $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                    return $this->redirect()->toRoute('settings/index', ['action' => 'paginators-default']);
                }else{
                    $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
                }
            }
        }
        return new ViewModel(['form' => $form]);
    }
    
    /**
     * id = supplies
     */
    public function suppliesDefaultAction(){
        $code = 'supplies';
        $form = new SuppliesDefaultForm();
        $arrCurrent = $this->entitySettings->fetchRow($code);
        if(empty($arrCurrent)){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý, đề nghị liên hệ quản trị viên để được xử lý trường hợp này!');
        }else{
            // Set value
            $content = json_decode($arrCurrent['content'], true);
            $form->setData($content);
            // Check request post
            $request = $this->getRequest();
            if($request->isPost()){
                $valuePost = $request->getPost()->toArray();
                $form->setData($valuePost);
                if($form->isValid()){
                    $dataArray = [
                        'time_limit_supplies_ins' => $valuePost['time_limit_supplies_ins'],
                        'time_limit_supplies_outs' => $valuePost['time_limit_supplies_outs'],
                        'time_limit_proposal_details' => $valuePost['time_limit_proposal_details']
                    ];
                    // data build to json
                    $data = [
                        'content' => json_encode($dataArray),
                    ];
                    $this->entitySettings->updateRow($code, $data);
                    $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                    return $this->redirect()->toRoute('settings/index', ['action' => 'supplies-default']);
                }else{
                    $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
                }
            }
        }
        return new ViewModel(['form' => $form]);
    }
    
    /**
     * messages company: column setting_messages
     */
    public function messagesAction(){
        $view = new ViewModel();
        $form = new MessagesForm($this->sessionContainer->id);

        $companyId = $this->defineCompanyId;
        
        if($this->sessionContainer->id == '1'){
            $optionCompanies = $this->entityCompanies->fetchAllToOptions();
            $form->get('company_id')->setValueOptions( [
                '' => '--- Chọn một công ty ---'] +
                $optionCompanies
            );
            $arrAllCompanies = $this->entityCompanies->fetchAll();
            if(!empty($arrAllCompanies)){
                $arrCompanies = [];
                foreach($arrAllCompanies as $item){
//                     $settingMessages = json_decode($item['setting_messages'], true);
                    $settingMessages = $this->entitySettings->fetchMessage($item['id']);
                    if(!empty($settingMessages)){
                        foreach($settingMessages as $keySettingMessage => $itemSettingMessages){
                            $arrCompanies[$item['id']][$keySettingMessage] = $itemSettingMessages;
                        }
                    }
                }
                $view->setVariable('arrCompanies', $arrCompanies);
//                 \Zend\Debug\Debug::dump($arrCompanies); die();
            }
        }
        
        if($companyId != null){
            $valuePost = [];
            $arrSettingMessages = $this->entitySettings->fetchMessage($companyId);
            if(!empty($arrSettingMessages)){
                foreach($arrSettingMessages as $key => $item){
                    $valuePost[$key] = $item;
                }
            }
            //\Zend\Debug\Debug::dump($valuePost); die();
            $form->setData($valuePost);
        }
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'message_success' => $valuePost['message_success'],
                    'message_invalid' => $valuePost['message_invalid'],
                    'message_checked' => $valuePost['message_checked'],
                    'message_outdate' => $valuePost['message_outdate']
                ];
                if(isset($valuePost['company_id'])){
                    $companyId = $valuePost['company_id'];
                }
                $this->entityCompanyConfigs->updateRow('messages', $companyId, ['content' => json_encode($data)]);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                return $this->redirect()->toRoute('settings/index', ['action' => 'messages']);
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $view->setVariable('form', $form);
        $view->setVariable('userId', $this->sessionContainer->id);
        
        return $view;
    }
    
    /**
     * messages company: column configs
     */
    public function configsAction(){
        $view = new ViewModel();
        $form = new ConfigsForm($this->sessionContainer->id);

        $companyId = $this->defineCompanyId;
        
        if($this->sessionContainer->id == '1'){
            $optionCompanies = $this->entityCompanies->fetchAllToOptions();
            $form->get('company_id')->setValueOptions( [
                '' => '--- Chọn một công ty ---'] +
                $optionCompanies
            );
            $arrAllCompanies = $this->entityCompanies->fetchAll();
            if(!empty($arrAllCompanies)){
                $arrCompanies = [];
                foreach($arrAllCompanies as $item){
                    $configs = $this->entitySettings->fetchPaginator($item['id']);
                    if(!empty($configs)){
                        foreach($configs as $keyConfig => $itemConfig){
                            $arrCompanies[$item['id']][$keyConfig] = $itemConfig;
                        }
                    }
                }
                $view->setVariable('arrCompanies', $arrCompanies);
                //\Zend\Debug\Debug::dump($arrCompanies); die();
            }
        }

        if($companyId != null){
            $valuePost = [];
            $arrConfigs = $this->entitySettings->fetchPaginator($companyId);
            if(!empty($arrConfigs)){
                foreach($arrConfigs as $key => $item){
                    $valuePost[$key] = $item;
                }
            }
            //\Zend\Debug\Debug::dump($valuePost); die();
            $form->setData($valuePost);
        }
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'per_page' => $valuePost['per_page'],
                    'per_pages' => $valuePost['per_pages'],
                    'page_range' => $valuePost['page_range']
                ];
                if(isset($valuePost['company_id'])){
                    $companyId = $valuePost['company_id'];
                }
                $this->entityCompanyConfigs->updateRow('paginators', $companyId, ['content' => json_encode($data)]);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                return $this->redirect()->toRoute('settings/index', ['action' => 'configs']);
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $view->setVariable('form', $form);
        $view->setVariable('userId', $this->sessionContainer->id);
        
        return $view;
    }
    
}
