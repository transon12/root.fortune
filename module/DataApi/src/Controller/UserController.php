<?php

namespace DataApi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Admin\Model\PxtAuthentication;
use DataApi\Core\DataApiCore;
use DataApi\Model\Users;
use Settings\Model\Companies;
use Settings\Model\CompanyConfigs;

class UserController extends DataApiCore{
    
    public $entityPxtAuthentication;
    public $entityCompanies;
    public $entityCompanyConfigs;
    public $entityUsers;
    private $infoResponse = [];
    private $arrRequest = [];
    private $userCurrent = [];

    public function __construct(PxtAuthentication $entityPxtAuthentication, Companies $entityCompanies, CompanyConfigs $entityCompanyConfigs, Users $entityUsers) {
        parent::__construct($entityPxtAuthentication);
        $this->entityPxtAuthentication  = $entityPxtAuthentication;
        $this->entityCompanies          = $entityCompanies;
        $this->entityCompanyConfigs     = $entityCompanyConfigs;
        $this->entityUsers              = $entityUsers;
    }
    
    public function indexAction(){
		die("DataApi / index / index");
    }
    
    public function loginAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $username = isset($this->arrRequest["username"]) ? $this->arrRequest["username"] : "";
        $password = isset($this->arrRequest["password"]) ? $this->arrRequest["password"] : "";
        if($username != "" && $password != ""){
            $userCurrent = $this->entityPxtAuthentication->checkUserExist1($username, md5($password));
            if(!empty($userCurrent)){
                $userCurrent["configs"] = json_decode($userCurrent["configs"], true);
                // Lấy thông tin công ty nếu có
                if($userCurrent["company_id"] != "" && $userCurrent["company_id"] != null){
                    $companyCurrent = $this->entityCompanies->fetchRow($userCurrent["company_id"]);
                    if(!empty($companyCurrent)){
                        $userCurrent["company_name"]    = $companyCurrent["name"];
                        $userCurrent["company_user_id"] = $companyCurrent["user_id"];
                        if($userCurrent["company_user_id"] == null || $userCurrent["company_user_id"] == ""){
                            $userCurrent["company_user_id"] = "0";
                        }
                        // Lấy logo công ty
                        $companyConfigCurrent = $this->entityCompanyConfigs->fetchRow(["company_id" => $userCurrent["company_id"], "id" => "displays"]);
                        $userCurrent["company_logo"] = "";
                        if(!empty($companyConfigCurrent)){
                            $content = json_decode($companyConfigCurrent["content"], true);
                            if(isset($content["logo"])){
                                if($content["logo"] != ""){
                                    $userCurrent["company_logo"] = "https://fortune500.vn/uploads/" . $content["logo"];
                                }
                            }
                        }
                        if($userCurrent["company_logo"] == ""){
                            $userCurrent["company_logo"] = "https://root.fortune500.vn/temps/app-assets/images/logo/logo.jpg";
                        }
                        $userCurrent["name"] = "";
                        $userCurrent["logo"] = "";
                    }
                }else{
                    $userCurrent["name"] = "Công ty TNHH Fortune500";
                    $userCurrent["logo"] = "https://root1.fortune500.vn/temps/app-assets/images/logo/logo.jpg";
                    $userCurrent["company_name"] = "";
                    $userCurrent["company_logo"] = "";
                    $userCurrent["company_user_id"] = "0";
                }
                $this->infoResponse["error"] = "0";
                $this->infoResponse["content"] = $userCurrent;
            }else{
                $this->infoResponse["error"] = "1";
                $this->infoResponse["message"] = "Tài khoản hoặc mật khẩu không chính xác";
            }
        }else{
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Thông tin chưa đầy đủ";
        }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }

    public function listAction()
    {
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $this->checkUser();
        // die("AGDS");
        // $keyword = isset($this->arrRequest["keyword"]) ? $this->arrRequest["keyword"] : "";
        if($this->userCurrent["company_id"] == ""){
        $arrUsers = $this->entityUsers->fetchAlls();
            $this->infoResponse["error"] = "0";
            $this->infoResponse["content"] = $arrUsers;
        }else{
            $this->infoResponse["error"] = "0";
            $this->infoResponse["message"] = "Bạn không đủ quyền ở đây!";
        }
        die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
    
    public function checkAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        // \Zend\Debug\Debug::dump($this->arrRequest);
        $username = isset($this->arrRequest["username"]) ? $this->arrRequest["username"] : "";
        $password = isset($this->arrRequest["password"]) ? $this->arrRequest["password"] : "";
        if($username != "" && $password != ""){
            $this->userCurrent = $this->entityPxtAuthentication->checkUserExist2($username, $password);
            if(!empty($this->userCurrent)){
                $this->userCurrent["configs"] = json_decode($this->userCurrent["configs"], true);
                $this->infoResponse["error"] = "0";
                $this->infoResponse["content"] = $this->userCurrent;
            }else{
                $this->infoResponse["error"] = "1";
                $this->infoResponse["message"] = "Tài khoản hoặc mật khẩu không chính xác";
            }
        }else{
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Thông tin chưa đầy đủ";
        }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }

    private function checkPost(){
        if(!$this->getRequest()->isPost()){
            $this->infoResponse["error"] = "1";
            die(\Zend\Json\Encoder::encode($this->infoResponse));
        }
    }

    private function checkUser()
    {
        $username = isset($this->arrRequest["username"]) ? $this->arrRequest["username"] : "";
        $password = isset($this->arrRequest["password"]) ? $this->arrRequest["password"] : "";
        if ($username != "" && $password != "") {
            $this->userCurrent = $this->entityPxtAuthentication->checkUserExist2($username, $password);
            if (!empty($this->userCurrent)) {
                // $this->companyId = $userCurrent["company_id"];
                $this->userId = $this->userCurrent["id"];
                return true;
            } else {
                $this->infoResponse["error"] = "1";
                $this->infoResponse["message"] = "Tài khoản hoặc mật khẩu không chính xác";
            }
        } else {
            $this->infoResponse["error"] = "1";
            $this->infoResponse["message"] = "Thông tin chưa đầy đủ";
        }
        die(\Zend\Json\Encoder::encode($this->infoResponse));
    }
}
