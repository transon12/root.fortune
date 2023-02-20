<?php

namespace DataApi\Controller;

use Admin\Model\PxtAuthentication;
use DataApi\Core\DataApiCore;
use DataApi\Model\Companies;
use DataApi\Model\CompanyConfigs;

class CompaniesController extends DataApiCore{
    
    public $entityPxtAuthentication;
    private $entityCompanies;
    private $entityCompanyConfigs;
    private $companyId;
    private $userId;
    private $infoResponse = [];
    private $arrRequest = [];

    public function __construct(PxtAuthentication $entityPxtAuthentication, Companies $entityCompanies, CompanyConfigs $entityCompanyConfigs) {
        parent::__construct($entityPxtAuthentication);
        $this->entityPxtAuthentication  = $entityPxtAuthentication;
        $this->entityCompanies          = $entityCompanies;
        $this->entityCompanyConfigs     = $entityCompanyConfigs;
    }
    
    /**
     * Lấy danh sách khách hàng theo user_id
     */
    public function indexAction(){
        $this->checkPost();
        $getJson = new \Zend\Json\Server\Request\Http();
        $this->arrRequest = \Zend\Json\Decoder::decode($getJson->getRawJson(), 1);
        $this->checkUser();
        // Nếu không phải là nhân viên công ty
        if($this->companyId != null || $this->companyId != ""){
            $this->infoResponse["error"] = "1";
            $this->infoResponse["content"] = "Phải đăng nhập bằng tài khoản của công ty fortune!";
        }else{
            $keyword = isset($this->arrRequest["keyword"]) ? $this->arrRequest["keyword"] : "";
            $limit = isset($this->arrRequest["limit"]) ? $this->arrRequest["limit"] : 10;
            $page = isset($this->arrRequest["page"]) ? $this->arrRequest["page"] : 1;
            $arrCompanies = $this->entityCompanies->fetchAllAsUserId($this->userId, ["keyword" => $keyword, "page" => $page, "limit" => $limit]);
            $resultCompanies = [];
            if(!empty($arrCompanies)){
                $companyIds = "";
                foreach($arrCompanies as $item){
                    $resultCompanies[$item["id"]] = $item;
                    if($companyIds != ""){
                        $companyIds .= ", ";
                    }
                    $companyIds .= "'" . $item["id"] . "'";
                    $resultCompanies[$item["id"]]["logo"] = "https://fortune.org/temps/app-assets/images/logo/logo.jpg";
                    $resultCompanies[$item["id"]]["date"] = date("m/Y", strtotime($item["created_at"]));
                }
                $arrCompanyConfigs = $this->entityCompanyConfigs->fetchAllAsCompanyIds($companyIds, "displays");
                if(!empty($arrCompanyConfigs)){
                    foreach($arrCompanyConfigs as $item){
                        $content = json_decode($item["content"], true);
                        if(isset($content["logo"])){
                            if($content["logo"] != ""){
                                $resultCompanies[$item["company_id"]]["logo"] = "https://fortune.org/uploads/" . $content["logo"];
                            }
                        }
                    }
                }
                // \Zend\Debug\Debug::dump($arrCompanyConfigs);
                // die();
            }
            $this->infoResponse["error"] = "0";
            $this->infoResponse["content"] = $resultCompanies;
        }
		die(\Zend\Json\Encoder::encode($this->infoResponse));
    }

    private function checkPost(){
        if(!$this->getRequest()->isPost()){
            $this->infoResponse["error"] = "1";
            die(\Zend\Json\Encoder::encode($this->infoResponse));
        }
    }

    private function checkUser(){
        $username = isset($this->arrRequest["username"]) ? $this->arrRequest["username"] : "";
        $password = isset($this->arrRequest["password"]) ? $this->arrRequest["password"] : "";
        if($username != "" && $password != ""){
            $userCurrent = $this->entityPxtAuthentication->checkUserExist2($username, $password);
            if(!empty($userCurrent)){
                $this->companyId = $userCurrent["company_id"];
                $this->userId = $userCurrent["id"];
                return true;
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
}
