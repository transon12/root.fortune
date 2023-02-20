<?php 

namespace Companies\Controller;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Companies\Form\OrderDetails\AddForm;
use Companies\Form\OrderDetails\DeleteForm;
use Companies\Model\OrderDetails;
use Companies\Model\Orders;
use Settings\Model\Settings;
use Settings\Model\Technologies;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class OrderDetailsController extends AdminCore{

    public $entitySettings;
    public $entityOrders;
    public $entityCompanies;
    public $entityAddresses;
    public $entitySurrogates;
    public $entityOrderDetails;
    public $entityTechnologies;
    public $entityUsers;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Orders $entityOrders, OrderDetails $entityOrderDetails, Technologies $entityTechnologies, Users $entityUsers){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityOrders = $entityOrders;
        $this->entityOrderDetails = $entityOrderDetails;
        $this->entityTechnologies = $entityTechnologies;
        $this->entityUsers = $entityUsers;
    }

    public function indexAction(){
        $arrUserId = $this->entityUsers->fetchRowAsId($this->sessionContainer->id);
        if($this->sessionContainer->id != '1' && ($arrUserId['company_id'] != "" || $arrUserId['company_id'] != null)){
            $this->redirect()->toRoute('admin/index', ['action' => 'index']);
        }
        $this->layout()->setTemplate('iframe/layout');
        $orderId = $this->params()->fromRoute('id', 0);
        // $arrOrderDetails = new Paginator(new ArrayAdapter( $this->entityOrderDetails->fetchAll($orderId)));
        $arrOrderDetails = $this->entityOrderDetails->fetchAll($orderId);
        
        // $page = (int) $this->params()->fromQuery('page', 1);
        // $page = ($page < 1) ? 1 : $page;
        // // get setting paginator
        // $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // // set per page
        // $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        // $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        // $arrOrderDetails->setCurrentPageNumber($page);
        // $arrOrderDetails->setItemCountPerPage($perPage);
        // $arrOrderDetails->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrOrderDetails' => $arrOrderDetails,
            'orderId' => $orderId,
            // 'contentPaginator' => $contentPaginator,
            'optionTechnologies' => $this->entityTechnologies->fetchAllToOption()
        ]);
    }

    public function addAction(){
        $arrUserId = $this->entityUsers->fetchRowAsId($this->sessionContainer->id);
        if($this->sessionContainer->id != '1' && ($arrUserId['company_id'] != "" || $arrUserId['company_id'] != null)){
            $this->redirect()->toRoute('admin/index', ['action' => 'index']);
        }
        $this->layout()->setTemplate('iframe/layout');
        $orderId = $this->params()->fromRoute('id', 0);
        $form = new AddForm();

        $optionTechnologies = $this->entityTechnologies->fetchAllToOption1(['orders_id' => $orderId]);
        //\Zend\Debug\Debug::dump($optionTechnologies); die();
        $form->get('technologies_id')->setValueOptions(['' => '--- Chọn một công nghệ ---'] + $optionTechnologies);
        $request = $this->getRequest();

        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'orders_id' => $orderId,
                    'technologies_id' => $valuePost['technologies_id'],
                    'status' => 1,
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entityOrderDetails->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                return $this->redirect()->toRoute('companies/order-details', ['action' => 'index', 'id' => $orderId]);
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        return new ViewModel([
            'form' => $form,
            'orderId' => $orderId,
            'optionTechnologies' => $optionTechnologies
        ]);
    }

    public function deleteAction(){
        $arrUserId = $this->entityUsers->fetchRowAsId($this->sessionContainer->id);
        if($this->sessionContainer->id != '1' && ($arrUserId['company_id'] != "" || $arrUserId['company_id'] != null)){
            $this->redirect()->toRoute('admin/index', ['action' => 'index']);
        }
        $this->layout()->setTemplate('empty/layout');
        $orderId = $this->params()->fromRoute('id',0);
        $detailId = $this->params()->fromRoute('detailid',0);
        $valueCurrent = $this->entityOrderDetails->fetchAll($orderId);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('companies/order-detail',['action' => 'index', 'id' => $orderId]);
        }
        $request = $this->getRequest();
        $form = new DeleteForm($request->getRequestUri());
        if($request->isPost()){
            $this->entityOrderDetails->updateRow( ['status' => '-1'],['orders_id'=>$orderId,'id'=>$detailId]);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        return new ViewModel([
            'form' => $form,
            'valueCurrent' => $valueCurrent,
            'orderId' => $orderId
        ]);
    }
}