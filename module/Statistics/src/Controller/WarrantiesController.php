<?php
namespace Statistics\Controller;

use Zend\View\Model\ViewModel;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Settings\Model\Settings;
use Statistics\Form\Warranties\AddForm;
use Statistics\Form\Warranties\DeleteForm;
use Statistics\Form\Warranties\EditForm;
use Statistics\Model\Warranties;
use Codes\Model\Codes;
use Admin\Model\Users;

class WarrantiesController extends AdminCore{
    private $entityWarranties;
    private $entitySettings;
    private $entityCodes;
    private $entityUsers;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, 
        Warranties $entityWarranties, Codes $entityCodes, Users $entityUsers) {
        parent::__construct($entityPxtAuthentication);
        $this->entityWarranties = $entityWarranties;
        $this->entitySettings = $entitySettings;
        $this->entityCodes = $entityCodes;
        $this->entityUsers = $entityUsers;
    }
    
    public function indexAction(){
        $codeId = $this->params()->fromRoute('id', 0);
        $this->checkCodeId($codeId);
        $this->layout()->setTemplate('iframe/layout');
        $arrWarranties = new Paginator(new ArrayAdapter( $this->entityWarranties->fetchAlls(['code_id' => $codeId]) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrWarranties->setCurrentPageNumber($page);
        $arrWarranties->setItemCountPerPage($perPage);
        $arrWarranties->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrWarranties' => $arrWarranties, 
            'contentPaginator' => $contentPaginator, 
            'codeId' => $codeId,
            'optionUsers' => $this->entityUsers->fetchAllOptions(COMPANY_ID)
        ]);
    }
    
    public function addAction(){
        $codeId = $this->params()->fromRoute('id', 0);
        $this->checkCodeId($codeId);
        $this->layout()->setTemplate('iframe/layout');
        $view = new ViewModel();
        $form = new AddForm();
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $datetimeReceive = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_receive']);
                $datetimeReturn = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_return']);
                $data = [
                    'company_id' => COMPANY_ID,
                    'code_id' => $codeId,
                    'user_id' => $this->sessionContainer->id,
                    'title' => $valuePost['title'],
                    'content' => $valuePost['content'],
                    'price' => $valuePost['price'],
                    'datetime_receive' => date_format($datetimeReceive, 'Y-m-d H:i:s'),
                    'datetime_return' => date_format($datetimeReturn, 'Y-m-d H:i:s'),
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entityWarranties->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                return $this->redirect()->toRoute('statistics/warranties', ['action' => 'index', 'id' => $codeId]);
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $view->setVariable('form', $form);
        $view->setVariable('codeId', $codeId);
        return $view;
    }
    
    public function editAction(){
        $codeId = $this->params()->fromRoute('id', 0);
        $this->checkCodeId($codeId);
        $this->layout()->setTemplate('iframe/layout');
        $view = new ViewModel();
        
        $request = $this->getRequest();
        $warrantyId = (int)$this->params()->fromRoute('warranty_id', 0);
        $valueCurrent = $this->entityWarranties->fetchRow(['id' => $warrantyId, 'code_id' => $codeId]);

        if(empty($valueCurrent)){
            $this->redirect()->toRoute('statistics/warranties', ['action' => 'index', 'id' => $codeId]);
        }else{
            $valuePost = $valueCurrent;
        }
        $form = new EditForm();
        
        //\Zend\Debug\Debug::dump($valueCurrent);
        //\Zend\Debug\Debug::dump($valuePost);
        //die('abc');
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $datetimeReceive = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_receive']);
                $datetimeReturn = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_return']);
                $data = [
                    'code_id' => $codeId,
                    'user_id' => $this->sessionContainer->id,
                    'title' => $valuePost['title'],
                    'content' => $valuePost['content'],
                    'price' => $valuePost['price'],
                    'datetime_receive' => date_format($datetimeReceive, 'Y-m-d H:i:s'),
                    'datetime_return' => date_format($datetimeReturn, 'Y-m-d H:i:s'),
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entityWarranties->updateRow($warrantyId, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }else{
            $valuePost['datetime_receive'] = date_format(date_create($valuePost['datetime_receive']), 'd/m/Y H:i:s');
            $valuePost['datetime_return'] = date_format(date_create($valuePost['datetime_return']), 'd/m/Y H:i:s');
        }
        $form->setData($valuePost);
        
        $view->setVariable('form', $form);
        $view->setVariable('codeId', $codeId);
        return $view;
    }
    
    public function checkCodeId($id = 0){
        $valueCurrent = $this->entityCodes->fetchRowId(COMPANY_ID, $id);
        // \Zend\Debug\Debug::dump($valueCurrent); die();
        if(empty($valueCurrent)){
            die('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
        }
        return true;
    }
    
    public function deleteAction(){
        $codeId = $this->params()->fromRoute('id', 0);

        $this->checkCodeId($codeId);
        
        $this->layout()->setTemplate('empty/layout');
        
        $request = $this->getRequest();
        $warrantyId = (int)$this->params()->fromRoute('warranty_id', 0);
        // echo $warrantyId; die();
        $valueCurrent = $this->entityWarranties->fetchRow(['id' => $warrantyId, 'code_id' => $codeId]);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('promotions/prizes');
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];

        if($request->isPost()){
            // delete prizes
            // die("abc");
            $this->entityWarranties->deleteRow(['id' => $warrantyId, 'code_id' => $codeId]);
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
