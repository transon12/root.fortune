<?php
namespace DataApi\Core;

use Admin\Model\PxtAuthentication;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

class DataApiCore extends AbstractActionController{
    
    public $entityPxtAuthentication;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication) {
        $this->entityPxtAuthentication = $entityPxtAuthentication;
    }
    
    public function onDispatch(MvcEvent $e){
        $this->paramController = $this->getEvent()->getRouteMatch()->getParam('controller');
        $this->paramAction = $this->getEvent()->getRouteMatch()->getParam('action');
        $this->routeName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        
        $arrRouteName = explode('/', $this->routeName);
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
        $getJson = new \Zend\Json\Server\Request\Http();
        $request = $this->getRequest();
        $dataLog['param']   = json_encode($this->params()->fromQuery());
        $dataLog['route']   = $request->getUriString();
        if($request->isPost()){
            $dataPosts = $request->getPost()->toArray();
            if(isset($dataPosts['password'])){
                $dataPosts['password'] = "*******";
            }
            $dataLog['post'] = $getJson->getRawJson();
        }
        $this->entityPxtAuthentication->addLog($dataLog);
        /**
         * add log when user is used
         */
        $response = parent::onDispatch($e);
        return $response;
    }
    
}
