<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Supplies\Controller;

use Zend\View\Model\ViewModel;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Supplies\Model\ProposalDetails;
use Supplies\Model\Supplies;
use Supplies\Form\SuppliesOuts\AddForm;
use Storehouses\Model\Storehouses;
use Supplies\Model\SuppliesIns;
use Supplies\Form\SuppliesOuts\EditForm;
use Supplies\Form\SuppliesOuts\DeleteForm;
use Supplies\Model\SuppliesOuts;

class SuppliesOutsController extends AdminCore{
    public $entitySettings;
    public $entityProposalDetails;
    public $entitySupplies;
    public $entitySuppliesOuts;
    public $entityStorehouses;
    public $entitySuppliesIns;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, SuppliesOuts $entitySuppliesOuts,
        ProposalDetails $entityProposalDetails, Supplies $entitySupplies, Storehouses $entityStorehouses, SuppliesIns $entitySuppliesIns) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entitySuppliesOuts = $entitySuppliesOuts;
        $this->entityProposalDetails = $entityProposalDetails;
        $this->entitySupplies = $entitySupplies;
        $this->entityStorehouses = $entityStorehouses;
        $this->entitySuppliesIns = $entitySuppliesIns;
    }
    
    public function addAction(){
        $proposalDetailId = (int)$this->params()->fromRoute('id', 0);
        $currentProposalDetail = $this->checkProposalDetailId($proposalDetailId);
        $supplyId = $currentProposalDetail['supply_id'];
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        $view = new ViewModel();
        $form = new AddForm($request->getRequestUri());
        // add data supplies
        $optionStorehouses = $this->entityStorehouses->fetchAllOptions(['company_id' => $this->defineCompanyId]);
        // get all number supplies in storehouse
        $arrSuppliesIns = $this->entitySuppliesIns->fetchAllsGroupStorehouseAsSupplyId($supplyId);
        //\Zend\Debug\Debug::dump($arrSuppliesIns);
        // get all number supplies in storehouse
        $arrSuppliesOuts = $this->entitySuppliesOuts->fetchAllsGroupStorehouseAsSupplyId($supplyId);
        //\Zend\Debug\Debug::dump($arrProposalDetailsStorehouses);
        // check supplies in storehouses remain?
        $optionStorehousesCheckedRemain = [];
        if(!empty($optionStorehouses)){
            foreach($optionStorehouses as $keyStorehouse => $itemStorehouse){
                $numberInStorehouse = isset($arrSuppliesIns[$keyStorehouse]) ? $arrSuppliesIns[$keyStorehouse] : '0';
                $numberInProposed = isset($arrSuppliesOuts[$keyStorehouse]) ? $arrSuppliesOuts[$keyStorehouse] : '0';
                $numberRemain = $numberInStorehouse - $numberInProposed;
                if($numberRemain > 0){
                    $optionStorehousesCheckedRemain[$keyStorehouse] = $itemStorehouse . ' (còn lại: ' . $numberRemain . ')';
                }
            }
        }
        $form->get('storehouse_id')->setValueOptions( ['' => '--- Chọn một kho ---' ] + $optionStorehousesCheckedRemain );
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            // check enough number in proposal
            $numberHasTaken = $this->entitySuppliesOuts->fetchTotalNumberAsProposalDetail($proposalDetailId);
            $numberWillTake = (int)$numberHasTaken + (int)$valuePost['number'];
            $numberRemainTake = (int)$currentProposalDetail['number'] - (int)$numberHasTaken;
            if($numberWillTake > $currentProposalDetail['number']){
                $form->get('number')->setMessages($form->get('number')->getMessages() + ['number_remain' => 'Số lượng lấy phải nhỏ hơn hoặc bằng ' . $numberRemainTake]);
                $isValid = false;
            }
//             echo $currentProposalDetail['number'] . '<br />';
//             echo $numberHasTaken . '<br />';
//             echo $numberWillTake . '<br />';
//             echo $numberRemainTake . '<br />';
//             die();
            if($isValid){
                $data = [
                    'proposal_detail_id' => $proposalDetailId,
                    'supply_id' => $supplyId,
                    'number' => $valuePost['number'],
                    'user_id' => $this->sessionContainer->id,
                    'storehouse_id' => $valuePost['storehouse_id'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entitySuppliesOuts->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
                //return $this->redirect()->toRoute('supplies/proposal-details', ['action' => 'index', 'id' => $proposalDetailId]);
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $view->setVariable('form', $form);
        $view->setVariable('proposalDetailId', $proposalDetailId);
        return $view;
    }
    
    public function editAction(){
        $proposalDetailId = (int)$this->params()->fromRoute('id', 0);
        $currentProposalDetail = $this->checkProposalDetailId($proposalDetailId);
        $supplyId = $currentProposalDetail['supply_id'];
        
        $this->layout()->setTemplate('empty/layout');
        
        $view = new ViewModel();
        
        $request = $this->getRequest();
        $suppliesOutId = (int)$this->params()->fromRoute('supplies_out_id', 0);
        $valueCurrent = $this->entitySuppliesOuts->fetchRow($suppliesOutId);
        if(empty($valueCurrent)){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('susscess');
        }else{
            // get setting
            $contentSupplies = $this->entitySettings->fetchSupplies($this->defineCompanyId);
            if(isset($contentSupplies['time_limit_supplies_outs'])){
                // get time current
                $timeCurrent = strtotime( \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent() );
                // get time in data
                $timeFinish = (int)$contentSupplies['time_limit_supplies_outs'] + strtotime($valueCurrent['created_at']);
                $timeRemain = $timeFinish - $timeCurrent;
                if((int)$contentSupplies['time_limit_supplies_outs'] < 0 || $timeRemain > 0){
                    $valuePost = $valueCurrent;
                }else{
                    $this->flashMessenger()->addWarningMessage('Không thể sửa dòng này vì đã hết thời gian cho phép!');
                    die('success');
                }
            }else{
                $valuePost = $valueCurrent;
            }
        }
        $form = new EditForm($request->getRequestUri());
        // add data supplies
        $optionStorehouses = $this->entityStorehouses->fetchAllOptions(['company_id' => $this->defineCompanyId]);
        // get all number supplies in storehouse
        $arrSuppliesIns = $this->entitySuppliesIns->fetchAllsGroupStorehouseAsSupplyId($supplyId);
        //\Zend\Debug\Debug::dump($arrSuppliesIns);
        // get all number supplies in storehouse
        $arrProposalDetailsStorehouses = $this->entitySuppliesOuts->fetchAllsGroupStorehouseAsSupplyId($supplyId);
        //\Zend\Debug\Debug::dump($arrProposalDetailsStorehouses);
        // check supplies in storehouses remain?
        $optionStorehousesCheckedRemain = [];
        if(!empty($optionStorehouses)){
            foreach($optionStorehouses as $keyStorehouse => $itemStorehouse){
                $numberInStorehouse = isset($arrSuppliesIns[$keyStorehouse]) ? $arrSuppliesIns[$keyStorehouse] : '0';
                $numberInProposed = isset($arrProposalDetailsStorehouses[$keyStorehouse]) ? $arrProposalDetailsStorehouses[$keyStorehouse] : '0';
                $numberRemain = $numberInStorehouse - $numberInProposed;
                // check with data current
                if($valueCurrent['storehouse_id'] == $keyStorehouse){
                    $numberRemain = (int)$numberRemain + (int)$valueCurrent['number'];
                }
                if($numberRemain > 0){
                    $optionStorehousesCheckedRemain[$keyStorehouse] = $itemStorehouse . ' (còn lại: ' . $numberRemain . ')';
                }
            }
        }
        $form->get('storehouse_id')->setValueOptions( ['' => '--- Chọn một kho ---' ] + $optionStorehousesCheckedRemain );
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = $form->isValid();
            // check enough number in proposal
            $numberHasTaken = $this->entitySuppliesOuts->fetchTotalNumberAsProposalDetail($proposalDetailId);
            // update numberhas taken
            $numberHasTaken = (int)$numberHasTaken - $valueCurrent['number'];
            $numberWillTake = (int)$numberHasTaken + (int)$valuePost['number'];
            $numberRemainTake = (int)$currentProposalDetail['number'] - (int)$numberHasTaken;
            if($numberWillTake > $currentProposalDetail['number']){
                $form->get('number')->setMessages($form->get('number')->getMessages() + ['number_remain' => 'Số lượng lấy phải nhỏ hơn hoặc bằng ' . $numberRemainTake]);
                $isValid = false;
            }
            if($isValid){
                $data = [
                    'number' => $valuePost['number'],
                    'storehouse_id' => $valuePost['storehouse_id'],
                    'user_id' => $this->sessionContainer->id,
                ];
                $this->entitySuppliesOuts->updateRow($suppliesOutId, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                die('success');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $form->setData($valuePost);
        
        $view->setVariable('form', $form);
        $view->setVariable('proposalDetailId', $proposalDetailId);
        return $view;
    }
    
    public function checkProposalDetailId($id = 0){
        $valueCurrent = $this->entityProposalDetails->fetchRow($id);
        if(empty($valueCurrent)){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('success');
        }
        return $valueCurrent;
    }
    
    public function deleteAction(){
        $proposalDetailId = (int)$this->params()->fromRoute('id', 0);
        $currentProposalDetail = $this->checkProposalDetailId($proposalDetailId);
        
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('supplies_out_id', 0);
        $valueCurrent = $this->entitySuppliesOuts->fetchRow($id);
        if(empty($valueCurrent)){
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết!');
            die('susscess');
        }else{
            // get setting
            $contentSupplies = $this->entitySettings->fetchSupplies($this->defineCompanyId);
            if(isset($contentSupplies['time_limit_supplies_outs'])){
                // get time current
                $timeCurrent = strtotime( \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent() );
                // get time in data
                $timeFinish = (int)$contentSupplies['time_limit_supplies_outs'] + strtotime($valueCurrent['created_at']);
                $timeRemain = $timeFinish - $timeCurrent;
                if((int)$contentSupplies['time_limit_supplies_outs'] < 0 || $timeRemain > 0){
                    $valuePost = $valueCurrent;
                }else{
                    $this->flashMessenger()->addWarningMessage('Không thể xóa dòng này vì đã hết thời gian cho phép!');
                    die('success');
                }
            }else{
                $valuePost = $valueCurrent;
            }
        }
        
        $form = new DeleteForm($request->getRequestUri());
        // check relationship
        if($request->isPost()){
            $this->entitySuppliesOuts->deleteRow($id);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }
        
        return new ViewModel([
			'form' => $form, 
			'valueCurrent' => $valueCurrent
	    ]);
    }
    
}
