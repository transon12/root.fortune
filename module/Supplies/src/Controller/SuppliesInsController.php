<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Supplies\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Supplies\Model\SuppliesIns;
use Storehouses\Model\Storehouses;
use Supplies\Form\SuppliesIns\AddForm;
use Supplies\Form\SuppliesIns\EditForm;
use Supplies\Form\SuppliesIns\DeleteForm;
use Supplies\Model\Supplies;
use Supplies\Model\Suppliers;

class SuppliesInsController extends AdminCore{
    public $entitySettings;
    public $entityStorehouses;
    public $entitySuppliesIns;
    public $entitySupplies;
    public $entitySuppliers;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, SuppliesIns $entitySuppliesIns, 
        Storehouses $entityStorehouses, Supplies $entitySupplies, Suppliers $entitySuppliers) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entitySuppliesIns = $entitySuppliesIns;
        $this->entityStorehouses = $entityStorehouses;
        $this->entitySupplies = $entitySupplies;
        $this->entitySuppliers = $entitySuppliers;
    }
    
    public function indexAction(){
        $storehouseId = (int)$this->params()->fromRoute('id', 0);
        $this->checkStorehouseId($storehouseId);
        $this->layout()->setTemplate('iframe/layout');
        $arrSuppliesIns = new Paginator(new ArrayAdapter( $this->entitySuppliesIns->fetchAlls(['storehouse_id' => $storehouseId]) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // get setting supplies
        $contentSupplies = $this->entitySettings->fetchSupplies($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrSuppliesIns->setCurrentPageNumber($page);
        $arrSuppliesIns->setItemCountPerPage($perPage);
        $arrSuppliesIns->setPageRange($contentPaginator['page_range']);

        // get data supplies
        $optionSupplies = $this->entitySupplies->fetchAllOptions();
        // get data suppliers
        $optionSuppliers = $this->entitySuppliers->fetchAllOptions();
        
        return new ViewModel([
            'arrSuppliesIns' => $arrSuppliesIns, 
            'contentPaginator' => $contentPaginator,
            'contentSupplies' => $contentSupplies,
            'storehouseId' => $storehouseId,
            'optionSupplies' => $optionSupplies,
            'optionSuppliers' => $optionSuppliers
        ]);
    }
    
    public function addAction(){
        $storehouseId = (int)$this->params()->fromRoute('id', 0);
        $this->checkStorehouseId($storehouseId);
        $this->layout()->setTemplate('iframe/layout');
        $view = new ViewModel();
        $form = new AddForm();
        // add data supplies
        $optionSupplies = $this->entitySupplies->fetchAllOptions(['company_id' => $this->defineCompanyId]);
        $form->get('supply_id')->setValueOptions( ['' => '--- Chọn một vật tư ---' ] + $optionSupplies );
        // add data suppliers
        $optionSuppliers = $this->entitySuppliers->fetchAllOptions(['company_id' => $this->defineCompanyId]);
        $form->get('supplier_id')->setValueOptions( ['' => '--- Chọn một nhà cung cấp ---' ] + $optionSuppliers );
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $valuePost['price'] = str_replace(' vnđ', '', $valuePost['price']);
            $valuePost['price'] = str_replace(',', '', $valuePost['price']);
            $valuePost['number'] = str_replace(',', '', $valuePost['number']);
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'storehouse_id' => $storehouseId,
                    'number' => $valuePost['number'],
                    'price' => $valuePost['price'],
                    'supplier_id' => $valuePost['supplier_id'],
                    'supply_id' => $valuePost['supply_id'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entitySuppliesIns->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                return $this->redirect()->toRoute('supplies/supplies-ins', ['action' => 'index', 'id' => $storehouseId]);
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $view->setVariable('form', $form);
        $view->setVariable('storehouseId', $storehouseId);
        return $view;
    }
    
    public function editAction(){
        $storehouseId = (int)$this->params()->fromRoute('id', 0);
        $this->checkStorehouseId($storehouseId);
        $this->layout()->setTemplate('iframe/layout');
        
        $view = new ViewModel();
        
        $request = $this->getRequest();
        $suppliesInId = (int)$this->params()->fromRoute('supplies_in_id', 0);
        $valueCurrent = $this->entitySuppliesIns->fetchRow($suppliesInId);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('supplies/supplies-ins');
        }else{
            // get setting
            $contentSupplies = $this->entitySettings->fetchSupplies($this->defineCompanyId);
            if(isset($contentSupplies['time_limit_supplies_ins'])){
                // get time current
                $timeCurrent = strtotime( \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent() );
                // get time in data
                $timeFinish = (int)$contentSupplies['time_limit_supplies_ins'] + strtotime($valueCurrent['created_at']);
                $timeRemain = $timeFinish - $timeCurrent;
                if((int)$contentSupplies['time_limit_supplies_ins'] < 0 || $timeRemain > 0){
                    $valuePost = $valueCurrent;
                }else{
                    $this->flashMessenger()->addWarningMessage('Không thể sửa dòng này vì đã hết thời gian cho phép!');
                    $this->redirect()->toRoute('supplies/supplies-ins');
                }
            }else{
                $valuePost = $valueCurrent;
            }
        }
        $form = new EditForm();
        // add data supplies
        $optionSupplies = $this->entitySupplies->fetchAllOptions(['company_id' => $this->defineCompanyId]);
        $form->get('supply_id')->setValueOptions( ['' => '--- Chọn một vật tư ---' ] + $optionSupplies );
        // add data suppliers
        $optionSuppliers = $this->entitySuppliers->fetchAllOptions(['company_id' => $this->defineCompanyId]);
        $form->get('supplier_id')->setValueOptions( ['' => '--- Chọn một nhà cung cấp ---' ] + $optionSuppliers );
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $valuePost['price'] = str_replace(' vnđ', '', $valuePost['price']);
            $valuePost['price'] = str_replace(',', '', $valuePost['price']);
            $valuePost['number'] = str_replace(',', '', $valuePost['number']);
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'storehouse_id' => $storehouseId,
                    'number' => $valuePost['number'],
                    'price' => $valuePost['price'],
                    'supplier_id' => $valuePost['supplier_id'],
                    'supply_id' => $valuePost['supply_id'],
                ];
                $this->entitySuppliesIns->updateRow($suppliesInId, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }else{
            $valuePost['price'] = number_format($valuePost['price'], 0, ".", ",");
            $valuePost['number'] = number_format($valuePost['number'], 0, ".", ",");
        }
        $form->setData($valuePost);
        
        $view->setVariable('form', $form);
        $view->setVariable('storehouseId', $storehouseId);
        return $view;
    }
    
    public function checkStorehouseId($id = 0){
        $valueCurrent = $this->entityStorehouses->fetchRow($id);
        if(empty($valueCurrent)){
            die('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết1!');
        }
        return true;
    }
    
    public function deleteAction(){
        $storehouseId = (int)$this->params()->fromRoute('id', 0);
        $this->checkStorehouseId($storehouseId);
        
        $this->layout()->setTemplate('empty/layout');
        
        $request = $this->getRequest();
        $suppliesInId = (int)$this->params()->fromRoute('supplies_in_id', 0);
        $valueCurrent = $this->entitySuppliesIns->fetchRow($suppliesInId);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('supplies/supplies-ins');
        }else{
            // get setting
            $contentSupplies = $this->entitySettings->fetchSupplies($this->defineCompanyId);
            if(isset($contentSupplies['time_limit_supplies_ins'])){
                // get time current
                $timeCurrent = strtotime( \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent() );
                // get time in data
                $timeFinish = (int)$contentSupplies['time_limit_supplies_ins'] + strtotime($valueCurrent['created_at']);
                $timeRemain = $timeFinish - $timeCurrent;
                if((int)$contentSupplies['time_limit_supplies_ins'] < 0 || $timeRemain > 0){
                    $valuePost = $valueCurrent;
                }else{
                    $this->flashMessenger()->addWarningMessage('Không thể xoá dòng này vì đã hết thời gian cho phép!');
                    die('success');
                }
            }else{
                $valuePost = $valueCurrent;
            }
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];
        if($request->isPost()){
            // delete prizes
            $this->entitySuppliesIns->deleteRow($suppliesInId);
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
