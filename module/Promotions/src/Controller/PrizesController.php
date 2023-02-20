<?php
namespace Promotions\Controller;

use Zend\View\Model\ViewModel;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Settings\Model\Settings;
use Promotions\Form\Prizes\AddForm;
use Promotions\Form\Prizes\DeleteForm;
use Promotions\Model\Dials;
use Promotions\Model\Prizes;
use Promotions\Form\Prizes\EditForm;
use Promotions\Model\WinnerDials;

class PrizesController extends AdminCore{
    private $entityDials;
    private $entitySettings;
    private $entityPrizes;
    private $entityWinnerDials;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, Dials $entityDials, 
        Prizes $entityPrizes, WinnerDials $entityWinnerDials) {
        parent::__construct($entityPxtAuthentication);
        $this->entityDials = $entityDials;
        $this->entitySettings = $entitySettings;
        $this->entityPrizes = $entityPrizes;
        $this->entityWinnerDials = $entityWinnerDials;
    }
    
    public function indexAction(){
        $dialId = (int)$this->params()->fromRoute('id', 0);
        $this->checkDialId($dialId);
        $this->layout()->setTemplate('iframe/layout');
        $arrPrizes = new Paginator(new ArrayAdapter( $this->entityPrizes->fetchAlls(['dial_id' => $dialId]) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrPrizes->setCurrentPageNumber($page);
        $arrPrizes->setItemCountPerPage($perPage);
        $arrPrizes->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrPrizes' => $arrPrizes, 
            'contentPaginator' => $contentPaginator, 
            'dialId' => $dialId
        ]);
    }
    
    public function addAction(){
        $dialId = (int)$this->params()->fromRoute('id', 0);
        $this->checkDialId($dialId);
        $this->layout()->setTemplate('iframe/layout');
        $view = new ViewModel();
        $form = new AddForm();
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'company_id' => $this->defineCompanyId,
                    'dial_id' => $dialId,
                    'name' => $valuePost['name'],
                    'number_win' => $valuePost['number_win'],
                    'time_dial' => $valuePost['time_dial'],
                    'price_topup' => $valuePost['price_topup'],
                    'message' => $valuePost['message'],
                    'status' => $valuePost['status'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entityPrizes->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                return $this->redirect()->toRoute('promotions/prizes', ['action' => 'index', 'id' => $dialId]);
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $view->setVariable('form', $form);
        $view->setVariable('dialId', $dialId);
        return $view;
    }
    
    public function editAction(){
        $dialId = (int)$this->params()->fromRoute('id', 0);
        $this->checkDialId($dialId);
        $this->layout()->setTemplate('iframe/layout');
        
        $view = new ViewModel();
        
        $request = $this->getRequest();
        $prizeId = (int)$this->params()->fromRoute('prizes_id', 0);
        $valueCurrent = $this->entityPrizes->fetchRow($prizeId);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('promotions/prizes');
        }else{
            $valuePost = $valueCurrent;
        }
        $form = new EditForm();
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $data = [
                    'name' => $valuePost['name'],
                    'number_win' => $valuePost['number_win'],
                    'time_dial' => $valuePost['time_dial'],
                    'price_topup' => $valuePost['price_topup'],
                    'message' => $valuePost['message'],
                    'status' => $valuePost['status']
                ];
                $this->entityPrizes->updateRow($prizeId, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
            }else{
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $form->setData($valuePost);
        
        $view->setVariable('form', $form);
        $view->setVariable('dialId', $dialId);
        return $view;
    }
    
    public function checkDialId($id = 0){
        $valueCurrent = $this->entityDials->fetchRow($id);
        //\Zend\Debug\Debug::dump($valueCurrent); die();
        if(empty($valueCurrent)){
            die('Có lỗi trong quá trình xử lý! Liên hệ Admin để biết thêm chi tiết1!');
        }
        return true;
    }
    
    public function deleteAction(){
        $dialId = (int)$this->params()->fromRoute('id', 0);
        $this->checkDialId($dialId);
        
        $this->layout()->setTemplate('empty/layout');
        
        $request = $this->getRequest();
        $prizeId = (int)$this->params()->fromRoute('prizes_id', 0);
        $valueCurrent = $this->entityPrizes->fetchRow($prizeId);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('promotions/prizes');
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];
//         $countWinnerDials = $this->entityWinnerDials->fetchCount(['prize_id' => $prizeId]);
//         if($countWinnerDials > 0){
//             $checkRelationship['winner_dials'] = 1;
//         }

        if($request->isPost()){
            // delete all list promotions
//             $this->entityWinnerDials->deleteRows(['prize_id' => $prizeId]);
            // delete prizes
            $this->entityPrizes->updateRow($prizeId, ['status' => '-1']);
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
