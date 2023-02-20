<?php

namespace Admin\Controller;

use Admin\Core\AdminCore;
use Admin\Form\LabourContracts\AddForm;
use Admin\Form\LabourContracts\DeleteForm;
use Admin\Form\LabourContracts\EditForm;
use Admin\Form\LabourContracts\SearchForm;
use Admin\Model\LabourContracts;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Settings\Model\Settings;
use Zend\Filter\File\RenameUpload;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;
use Zend\View\View;

class LabourContractsController extends AdminCore{
    
    public $entitySettings;
    public $entityLabourContracts;
    public $entityUsers;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    LabourContracts $entityLabourContracts, Users $entityUsers){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityLabourContracts = $entityLabourContracts;
        $this->entityUsers= $entityUsers;
    }

    public function indexAction(){
        $userId = $this->sessionContainer->id;
        // $userId = (int)$this->params()->fromRoute('id', 0);
        // if($userIdLogin != $userId){
        //     $this->flashMessenger()->addWarningMessage('Bạn không có quyền vào đây!');
        //     die();
        // }
        $formSearch = new SearchForm;
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        $valueCurrent = $this->entityUsers->fetchRowAsId($userId);
        if(empty($valueCurrent)){
            die('Not found!');
        }
        $arrContracts = $this->entityLabourContracts->fetchAlls($userId);
        
        // $page = (int) $this->params()->fromQuery('page',1);
        // $page = ($page < 1) ? 1 : $page;

        // $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        
        // $perPage = (int) $this->params()->fromQuery('per_page',$contentPaginator['per_page']);
        // $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;

        // $arrContracts->setCurrentPageNumber($page);
        // $arrContracts->setItemCountPerPage($perPage);
        // $arrContracts->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'formSearch' => $formSearch,
            'arrContracts' => $arrContracts,
            // 'contentPaginator' => $contentPaginator,
            'queries' => $queries,
            'userId'=> $userId,
            'valueCurrent' => $valueCurrent,
        ]);
    }

    public function viewsAction(){
        $userIdLogin = $this->sessionContainer->id;
        $userId = (int)$this->params()->fromRoute('id', 0);
        // if($userIdLogin != $userId){
        //     $this->flashMessenger()->addWarningMessage('Bạn không có quyền vào đây!');
        //     die();
        // }
        $formSearch = new SearchForm;
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        $valueCurrent = $this->entityUsers->fetchRowAsId($userId);
        if(empty($valueCurrent)){
            die('Not found!');
        }
        $arrContracts = new Paginator(new ArrayAdapter($this->entityLabourContracts->fetchAlls($userId)));
        
        $page = (int) $this->params()->fromQuery('page',1);
        $page = ($page < 1) ? 1 : $page;

        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        
        $perPage = (int) $this->params()->fromQuery('per_page',$contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;

        $arrContracts->setCurrentPageNumber($page);
        $arrContracts->setItemCountPerPage($perPage);
        $arrContracts->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'formSearch' => $formSearch,
            'arrContracts' => $arrContracts,
            'contentPaginator' => $contentPaginator,
            'queries' => $queries,
            'userId'=> $userId,
            'valueCurrent' => $valueCurrent,
        ]);
    }

    private function uploadFile($file = null,$path, $reName){
        $url = $path."/" . time() . '-' . $reName;
        $fileUpload = new RenameUpload([
            'target' => FILES_UPLOAD . $url,
            'randomize' => false
        ]);
        // \Zend\Debug\Debug::dump($url) ; die();
        $fileUpload->filter($file);
        return $url;
    }

    public function addAction(){
        $this->layout()->setTemplate('empty/layout');
        $userId = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityUsers->fetchRowAsId($userId);
        if(empty($valueCurrent)){
            die('Not found!');
        }
        $request = $this->getRequest();
        $form = new AddForm($request->getRequestUri());
        if($request->isPost()){
            $valuePost = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $folderPath = "fortune";
            $form->setData($valuePost);
            if($form->isValid()){
                $file = $valuePost["file"];
                $reName = \Pxt\String\ChangeString::changeSlug($file["name"]);

                $valuePost['begined_at'] = (isset($valuePost['begined_at']) && $valuePost['begined_at'] != "") ? $valuePost['begined_at'] : (date("d/m/Y H:i:s", time()));
                $valuePost['ended_at'] = (isset($valuePost['ended_at'])) ? $valuePost['ended_at'] : (date("d/m/Y H:i:s", time()));
                $beginedAt = date_create_from_format('d/m/Y H:i:s', $valuePost['begined_at']);
                $endAt = date_create_from_format('d/m/Y H:i:s', $valuePost['ended_at']);
                $data = [
                    'user_id' => $userId,
                    'begined_at' =>date_format($beginedAt, 'Y-m-d H:i:s'),
                    'ended_at' =>(empty($valuePost['ended_at'])) ? null : date_format($endAt, 'Y-m-d H:i:s'),
                    'url' => ($valuePost["file"]["size"] !== 0) ? $this->uploadFile($file, $folderPath, $reName) : null,
                    'status' => '1',
                    'created_at'=> \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entityLabourContracts->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
            // $form->get('begined_at')->setValue('');
            // $form->get('ended_at')->setValue('');
        }
        return new ViewModel([
            'form' => $form,
            'userId' => $userId,
            'valueCurrent' => $valueCurrent,
        ]);
    }

    public function editAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $userId = (int)$this->params()->fromRoute('id', 0);
        $contractId = (int)$this->params()->fromRoute('labour-contracts_id', 0);
        //Check user exist
        $valueCurrent = $this->entityUsers->fetchRow($userId);
        if(empty($valueCurrent)){
            die('Not found!');
        }
        //Check Contract exist
        $valueCurrentContract = $this->entityLabourContracts->fetchRow($contractId);
        if(empty($valueCurrentContract)){
            die('Not found!');
        }else{
            $valuePost = $valueCurrentContract; 
            // \Zend\Debug\Debug::dump($valueCurrentContract['url']);
            $valuePost['begined_at'] = (isset($valuePost['begined_at'])) ? date('d/m/Y H:i:s',strtotime($valuePost['begined_at'])) : '';
            $valuePost['ended_at'] = (isset($valuePost['ended_at'])) ? date('d/m/Y H:i:s',strtotime($valuePost['ended_at'])) : '';
        }
        $request = $this->getRequest();
        $form = new EditForm($request->getRequestUri());
        if($request->isPost()){
            $valuePost = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $folderPath = "fortune";
            $form->setData($valuePost);

            if($form->isValid()){
                $file = $valuePost["file"];
                $reName = \Pxt\String\ChangeString::changeSlug($file["name"]);
                
                $valuePost['begined_at'] = (isset($valuePost['begined_at']) && $valuePost['begined_at'] != "") ? $valuePost['begined_at'] : (date("d/m/Y H:i:s", time()));
                $beginedAt = date_create_from_format('d/m/Y H:i:s', $valuePost['begined_at']);
                $endAt = date_create_from_format('d/m/Y H:i:s', $valuePost['ended_at']);
                $data = [
                    'begined_at' =>date_format($beginedAt, 'Y-m-d H:i:s'),
                    'ended_at' =>(empty($endAt)) ? null : date_format($endAt, 'Y-m-d H:i:s'),
                    'url' => ($valuePost["file"]["size"] !== 0) ? $this->uploadFile($file, $folderPath, $reName) : $valueCurrentContract['url'],
                ];
                $this->entityLabourContracts->updateRow($userId, $contractId, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }else{
            $form->setData($valuePost);
        }
        $view->setVariable('form', $form);
        $view->setVariable('userId', $userId);
        return $view;
    }

    public function deleteAction(){
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        $userId = (int)$this->params()->fromRoute('id', 0);
        $contractId = (int)$this->params()->fromRoute('labour-contracts_id', 0);
        $form = new DeleteForm($request->getRequestUri());
        if($request->isPost()){
            $data = ['status' => '-1'];
            $this->entityLabourContracts->updateRow($userId,$contractId,$data);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        return new ViewModel([
            'form' => $form
        ]);
    }

    public function viewAction(){
        $this->layout()->setTemplate('empty/layout');
        $userId = (int)$this->params()->fromRoute('id', 0);
        // $userId = $this->sessionContainer->id;
        $labourContractsId = (int)$this->params()->fromRoute('labour-contracts_id', 0);

        $valueCurrent = $this->entityUsers->fetchRow($userId);
        if(empty($valueCurrent)){
            die('Not found!');
        }
        //Check Contract exist
        $valueCurrentContract = $this->entityLabourContracts->fetchRow($labourContractsId, $userId);
        if(empty($valueCurrentContract)){
            die('Not found!');
        }
        $filePath = $valueCurrentContract['url'];
        if($filePath == ""){
            die("Không tìm thấy hợp đồng");
        }
        return new ViewModel([
            'filePath' => $filePath,
        ]);
    }
}