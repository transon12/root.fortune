<?php
namespace Admin\Core;

use Zend\Mvc\Controller\AbstractActionController;
use Admin\Model\PxtAuthentication;
use Zend\Mvc\Console\Router\RouteMatch;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Admin\Form\Index\LoginForm;
use Zend\Authentication\Result;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Session\Config\SessionConfig;

class AdminCore extends AbstractActionController{
    
    public $entityPxtAuthentication;
    public $auth;
    public $paramController;
    public $paramAction;
    public $routeName;
    public $sessionManager;
    public $sessionContainer;
    public $defineCompanyId;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication) {
        $this->entityPxtAuthentication = $entityPxtAuthentication;
    }
    
    /**
     * Check session and re-config onDispatch
     */
    public function onDispatch(MvcEvent $e){
        $this->defineCompanyId = COMPANY_ID;
        //die(FULL_SERVER_NAME);
        // Get 'controller' parameter.
        $this->paramController = $this->getEvent()->getRouteMatch()->getParam('controller');
        // Get 'action' parameter.
        $this->paramAction = $this->getEvent()->getRouteMatch()->getParam('action');
        
        // Get name of matched route.
        $this->routeName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        
        $arrRouteName = explode('/', $this->routeName);
        //\Zend\Debug\Debug::dump($arrRouteName);
        if(count($arrRouteName) == 1){
            $this->routeName .= '/' . $this->paramAction;
        }
        /**
         * add log when user is used
         */
        $arrParamController = explode('\\', $this->paramController);
        $dataLog = [
            'domain' => FULL_SERVER_NAME,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'module' => isset($arrParamController[0]) ? $arrParamController[0] : "",
            'controller' => isset($arrParamController[2]) ? $arrParamController[2] : "",
            'action' => $this->paramAction,
            'content_server' => json_encode($_SERVER),
            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
        ];
        $request = $this->getRequest();
        $dataLog['param']   = json_encode($this->params()->fromQuery());
        $dataLog['route']   = $request->getUriString();
        if($request->isPost()){
            $dataPosts = $request->getPost()->toArray();
            if(isset($dataPosts['password'])){
                $dataPosts['password'] = "*******";
            }
            $dataLog['post'] = json_encode($dataPosts + $request->getFiles()->toArray());
        }
        /**
         * add log when user is used
         */
        if(!$this->checkSession()){
            $query = $this->params()->fromQuery();
            if(isset($query['modal'])){
                die('success');
            }
            $routeMatch = new \Zend\Router\RouteMatch(array(
                'controller' => $this->paramController,
                'action' => 'login'
            ));
                
            $routeMatch->setMatchedRouteName($this->routeName);
            $e->setRouteMatch($routeMatch);
            // Execute the request
            $response = parent::onDispatch($e);
            // add log
            $dataLog['action']  = "login";
            $dataLog['user_id'] = \Admin\Service\Authentication::getId();
            $this->entityPxtAuthentication->addLog($dataLog);
            return $response;
        }else{
            $dataLog['user_id'] = \Admin\Service\Authentication::getId();
            // check permission
            if(!$this->checkPermission()){
                
                $modal = $this->params()->fromQuery('modal', 0);
                if($modal == 1){
                    $routeMatch = new \Zend\Router\RouteMatch(array(
                        'controller' => $this->paramController,
                        'action' => 'ajax-not-permission'
                    ));
                }else{
                    $routeMatch = new \Zend\Router\RouteMatch(array(
                        'controller' => $this->paramController,
                        'action' => 'not-permission'
                    ));
                }
                $routeMatch->setMatchedRouteName($this->routeName);
                $e->setRouteMatch($routeMatch);
                // Execute the request
                $response = parent::onDispatch($e);
                if($modal == 1){
                    $this->layout()->setTemplate('empty/layout');
                }
                //add log
                $dataLog['action'] = ($modal == 1) ? "ajax-not-permission" : "not-permission";
                $this->entityPxtAuthentication->addLog($dataLog);
                return $response;
            }else{
                $this->layout()->setTemplate('admin/layout');
                // add log
                $this->entityPxtAuthentication->addLog($dataLog);
            }
        }
        
        $response = parent::onDispatch($e);
        
        return $response;
    }
    
    /**
     * Check session exist???
     * @return boolean
     */
    private function checkSession(){
        // install session
        $this->sessionManager = $this->getEvent()->getApplication()->getServiceManager()->get(SessionManager::class);
        $this->sessionContainer = new Container('session_login', $this->sessionManager);
        // \Zend\Debug\Debug::dump($this->sessionContainer->username); die();
        if(isset($this->sessionContainer->username)){
            // re-check session
            $userCurrent = $this->entityPxtAuthentication->checkUserExist($this->sessionContainer->username, $this->sessionContainer->password);
            // \Zend\Debug\Debug::dump($userCurrent); die();
            if(!empty($userCurrent)){
                // set session
                $this->setSessionUser($userCurrent + array('remember_me' => $this->sessionContainer->remember_me));
                return true;
            }else{
                // clear session
                $this->sessionContainer->getManager()->getStorage()->clear('session_login');
                return false;
            }
        }else{
            return false;
        }
    }
    
    /**
     * Show login form if not yet config session
     * @return \Zend\View\Model\ViewModel
     */
    public function loginAction(){ 
        $this->layout()->setTemplate('login/layout');
        // call form
        $form = new LoginForm();
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = $form->getData();
                $userCurrent = $this->entityPxtAuthentication->checkUserExist($data['username'], md5($data['password']));
                if(!empty($userCurrent)){
                    // set session
                    $this->setSessionUser($userCurrent + array('remember_me' => $data['remember_me']));
                    // flash message success
                    $this->flashMessenger()->addSuccessMessage('Đăng nhập thành công!');
                    return $this->redirect()->toUrl($_SERVER['REQUEST_URI']);
                }else{
                    $this->flashMessenger()->addWarningMessage('Tên đăng nhập hoặc mật khẩu không chính xác!');
                }
            }else{
                $this->flashMessenger()->addWarningMessage('Dữ liệu nhập sai!');
            }
        }
        // Set view
        $view = new ViewModel([
            'form' => $form
        ]);
        // Change view
        $view->setTemplate('admin/index/login');
        return $view;
    }
    
    /**
     * 
     */
    public function notPermissionAction(){ 
        $this->layout()->setTemplate('not-permission/layout');
        // Set view
        $view = new ViewModel();
        // Change view
        $view->setTemplate('admin/index/not-permission');
        return $view;
    }
    
    /**
     * 
     */
    public function ajaxNotPermissionAction(){ 
        $this->layout()->setTemplate('ajax-not-permission/layout');
        // Set view
        $view = new ViewModel();
        // Change view
        $view->setTemplate('admin/index/ajax-not-permission');
        return $view;
    }
    
    /**
     * remove session and redirect to admin page
     * @return \Zend\View\Model\ViewModel
     */
    public function logoutAction(){
        //$this->sessionContainer = new Container('session_login', $this->sessionManager);
        $this->sessionContainer->getManager()->getStorage()->clear('session_login');
        if(isset($_COOKIE['checkUsername'])){
            setcookie('checkUsername', '', time() - 86400, '/');
        }
        return $this->redirect()->toRoute('admin');
    }
    
    /**
     * Set session
     */
    public function setSessionUser($userCurrent){
        
        // reset company id
        $this->defineCompanyId = $userCurrent['company_id'];
        $settingConfig = $this->entityPxtAuthentication->fetchSetting('config');
        //\Zend\Debug\Debug::dump($settingConfig); die();
        // Check remember?
        if($userCurrent['remember_me'] == 1){
            $sessionConfig = new SessionConfig();
            $sessionConfig->setOptions(array(
                'cookie_lifetime' => isset($settingConfig['remember_me']) ? $settingConfig['remember_me'] : (60*60*24*30),
                'gc_maxlifetime' => isset($settingConfig['remember_me']) ? $settingConfig['remember_me'] : (60*60*24*365),
            ));
            $this->sessionManager->setConfig($sessionConfig);
        }else{
            $sessionConfig = new SessionConfig();
            $sessionConfig->setOptions(array(
                'cookie_lifetime' => isset($settingConfig['cookie_lifetime']) ? $settingConfig['cookie_lifetime'] : (60*60),
                'gc_maxlifetime' => isset($settingConfig['cookie_lifetime']) ? $settingConfig['cookie_lifetime'] : (60*60),
            ));
            $this->sessionManager->setConfig($sessionConfig);
        }
        //$this->sessionContainer = new Container('session_login', $this->sessionManager);
        $this->sessionContainer->id = $userCurrent['id'];
        $this->sessionContainer->company_id = $userCurrent['company_id'];
        $this->sessionContainer->username = $userCurrent['username'];
        $this->sessionContainer->password = $userCurrent['password'];
        $this->sessionContainer->firstname = $userCurrent['firstname'];
        $this->sessionContainer->lastname = $userCurrent['lastname'];
        $this->sessionContainer->gender = $userCurrent['gender'];
        $this->sessionContainer->remember_me = $userCurrent['remember_me'];
        $this->sessionContainer->configs = json_decode($userCurrent['configs'], true);
        
        $this->sessionContainer->groups_id = $userCurrent['groups_id'];
        $this->sessionContainer->permissions = $userCurrent['permissions'];
        $this->sessionContainer->not_permissions = $userCurrent['not_permissions'];
        $this->sessionContainer->groups_min_level = $userCurrent['groups_min_level'];
        $this->sessionContainer->groups_max_level = $userCurrent['groups_max_level'];
        $this->sessionContainer->positions_min_level = $userCurrent['positions_min_level'];
        $this->sessionContainer->positions_max_level = $userCurrent['positions_max_level'];
        
        new \Admin\Service\Authentication($userCurrent);

        setcookie('checkUsername', $userCurrent['username'], time() + 86400, '/', "", 0);
    }
    
    /**
     * Check permission
     */
    public function checkPermission(){
        // Check session
        //$this->sessionManager = $this->getEvent()->getApplication()->getServiceManager()->get(SessionManager::class);
        //$this->sessionContainer = new Container('session_login', $this->sessionManager);
        // not check permisstion if 'administrator'
        //echo \Zend\Debug\Debug::dump($this->sessionContainer->groups_id);
        //die('<br />aaa');
        if($this->sessionContainer->id == '1'){
            return true;
        }
        // echo $this->routeName;
        // echo $this->paramAction; die();
        // check this action present
        $actionPresent = $this->entityPxtAuthentication->fetchActionPresent($this->routeName, $this->paramAction);
          
        if($actionPresent == false){
            return true;
        }
        // \Zend\Debug\Debug::dump($this->sessionContainer->permissions);
        // \Zend\Debug\Debug::dump($actionPresent); die();
        if(in_array($actionPresent['id'], $this->sessionContainer->permissions)){
            return true;
        }
        return false;
    }
    
}
