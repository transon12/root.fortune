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
use Supplies\Model\ProposalDetails;
use Supplies\Model\Proposals;
use Supplies\Form\ProposalDetails\AddForm;
use Supplies\Form\ProposalDetails\EditForm;
use Supplies\Form\ProposalDetails\DeleteForm;
use Supplies\Model\Supplies;
use Storehouses\Model\Storehouses;

class ProposalDetailsController extends AdminCore{
    public $entitySettings;
    public $entityProposals;
    public $entityProposalDetails;
    public $entitySupplies;
    public $entityStorehouses;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, ProposalDetails $entityProposalDetails, 
        Proposals $entityProposals, Supplies $entitySupplies, Storehouses $entityStorehouses) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityProposalDetails = $entityProposalDetails;
        $this->entityProposals = $entityProposals;
        $this->entitySupplies = $entitySupplies;
        $this->entityStorehouses = $entityStorehouses;
    }
    
    public function indexAction(){
        $proposalId = (int)$this->params()->fromRoute('id', 0);
        $arrProposal = $this->checkProposalId($proposalId);
        $this->layout()->setTemplate('iframe/layout');
        $arrProposalDetails = new Paginator(new ArrayAdapter( $this->entityProposalDetails->fetchAlls(['proposal_id' => $proposalId]) ));
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
        
        $arrProposalDetails->setCurrentPageNumber($page);
        $arrProposalDetails->setItemCountPerPage($perPage);
        $arrProposalDetails->setPageRange($contentPaginator['page_range']);
        
        // get data supplies
        $optionSupplies = $this->entitySupplies->fetchAllOptions();
        // get data storehouses
        $optionStorehouses = $this->entityStorehouses->fetchAllOptions(['company_id' => $this->defineCompanyId]);
        return new ViewModel([
            'arrProposalDetails' => $arrProposalDetails, 
            'contentPaginator' => $contentPaginator, 
            'optionSupplies' => $optionSupplies,
            'proposalId' => $proposalId,
            'optionStorehouses' => $optionStorehouses,
            'permission' => \Pxt\Permission\Check::checkExportSupplies($this->sessionContainer->permissions),
            'contentSupplies' => $contentSupplies,
            'checkUserCreated' => \Pxt\Permission\Check::checkUserCreated($this->sessionContainer->id, $arrProposal['user_id']),
            'arrProposal' => $arrProposal
        ]);
    }
    
    public function addAction(){
        $proposalId = (int)$this->params()->fromRoute('id', 0);
        $arrProposal = $this->checkProposalId($proposalId);
        $this->layout()->setTemplate('iframe/layout');
        $view = new ViewModel();
        $form = new AddForm();
        // add data supplies
        $optionSupplies = $this->entitySupplies->fetchAllOptions();
        $form->get('supply_id')->setValueOptions( ['' => '--- Chọn một vật tư ---' ] + $optionSupplies );
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'proposal_id' => $proposalId,
                    'number' => $valuePost['number'],
                    'supply_id' => $valuePost['supply_id'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entityProposalDetails->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                return $this->redirect()->toRoute('supplies/proposal-details', ['action' => 'index', 'id' => $proposalId]);
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $view->setVariable('form', $form);
        $view->setVariable('proposalId', $proposalId);
        return $view;
    }
    
    public function editAction(){
        $proposalId = (int)$this->params()->fromRoute('id', 0);
        $arrProposal = $this->checkProposalId($proposalId);
        $this->layout()->setTemplate('iframe/layout');
        
        $view = new ViewModel();
        
        $request = $this->getRequest();
        $proposalDetailId = (int)$this->params()->fromRoute('proposal_detail_id', 0);
        $valueCurrent = $this->entityProposalDetails->fetchRow($proposalDetailId);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('supplies/proposal-details');
        }else{
            $valuePost = $valueCurrent;
        }
        $form = new EditForm('edit');
        // add data supplies
        $optionSupplies = $this->entitySupplies->fetchAllOptions();
        $form->get('supply_id')->setValueOptions( ['' => '--- Chọn một vật tư ---' ] + $optionSupplies );
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $valuePost['supply_id'] = $valueCurrent['supply_id'];
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'number' => $valuePost['number']
                ];
                $this->entityProposalDetails->updateRow($proposalDetailId, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $form->setData($valuePost);
        
        $view->setVariable('form', $form);
        $view->setVariable('proposalId', $proposalId);
        return $view;
    }
    
    public function checkProposalId($id = 0){
        $valueCurrent = $this->entityProposals->fetchRow($id);
        if(empty($valueCurrent)){
            die('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết1!');
        }
        return $valueCurrent;
    }
    
    public function deleteAction(){
        $proposalId = (int)$this->params()->fromRoute('id', 0);
        $arrProposal = $this->checkProposalId($proposalId);
        
        $this->layout()->setTemplate('empty/layout');
        
        $request = $this->getRequest();
        $proposalDetailId = (int)$this->params()->fromRoute('proposal_detail_id', 0);
        $valueCurrent = $this->entityProposalDetails->fetchRow($proposalDetailId);
        if(empty($valueCurrent)){
            $this->flashMessenger()->addWarningMessage('Lỗi dữ liệu, đề nghị liên hệ Admin!');
            die('success');
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];
        if($request->isPost()){
            // delete prizes
            $this->entityProposalDetails->deleteRow($proposalDetailId);
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
