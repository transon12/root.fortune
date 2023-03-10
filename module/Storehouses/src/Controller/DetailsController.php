<?php
namespace Storehouses\Controller;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Codes\Model\Codes;
use Settings\Model\Settings;
use Storehouses\Form\Details\AddForm;
use Storehouses\Form\Details\DeleteForm;
use Storehouses\Form\Details\ResetForm;
use Storehouses\Model\Agents;
use Storehouses\Model\BillDetails;
use Storehouses\Model\Bills;
use Storehouses\Model\Products;
use Storehouses\Model\Storehouses;
use Zend\View\Model\ViewModel;

class DetailsController extends AdminCore{
    public $entityStorehouses;
    public $entitySettings;
    public $entityCountries;
    public $entityCities;
    public $entityDistricts;
    public $entityWards;
    public $entityCompanies;
    public $entityUsers;
    public $entityProducts;
    public $entityAgents;
    public $entityBills;
    public $entityBillDetails;
    public $entityCodes;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Storehouses $entityStorehouses,Products $entityProducts , Agents $entityAgents ,
    Bills $entityBills,Users $entityUsers, BillDetails $entityBillDetails, Codes $entityCodes){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityStorehouses = $entityStorehouses;
        $this->entityUsers = $entityUsers;
        $this->entityProducts = $entityProducts;
        $this->entityAgents = $entityAgents;
        $this->entityBills = $entityBills;
        $this->entityBillDetails = $entityBillDetails;
        $this->entityCodes = $entityCodes;
    }

    public function indexAction(){
        $this->layout()->setTemplate('iframe/layout');
        $form = new ResetForm();
        $request = $this->getRequest();
        $agentId = (int)$this->params()->fromRoute('agentid', 0);
        $billId =(int)$this->params()->fromRoute('id', 0);
        $arrDetails = $this->entityBillDetails->fetchAll($billId);
        $optionProducts = $this->entityProducts->fetchAllOptions01(['company_id' => $this->defineCompanyId]);
        $arrCodes = [];
        
        if($request->isPost()){
            
            $valuePost = $request->getPost()->toArray();
            //\Zend\Debug\Debug::dump($valuePost); die();
            $form->setData($valuePost);
            if($form->isValid()){
                $options = [
                    'is_serial' => '1',
                    'value_begin' => null,
                    'value_end' => null
                ];
                $arrQrcodes = explode('=', $valuePost['codes']);
                if(isset($arrQrcodes[1])){
                    $options['value_begin'] = $arrQrcodes[1];
                }else{
                    $arrSerials = explode('-', $valuePost['codes']);
                    if(!isset($arrSerials[1])){
                        $options['is_serial'] = 2; 
                        $options['value_begin'] = trim($arrSerials[0]);
                    }else{
                        $options['is_serial'] = 3;
                        $options['value_begin'] = trim($arrSerials[0]);
                        $options['value_end'] = trim($arrSerials[1]);
                        $serialBegin = $this->entityCodes->fetchOneRowAsSerial($this->defineCompanyId, trim($arrSerials[0]));
                        $serialEnd = $this->entityCodes->fetchOneRowAsSerial($this->defineCompanyId,trim($arrSerials[1]));      
                    }
                }
                $arrCodes = $this->entityCodes->fetchAlls($this->defineCompanyId, ['import' => '1', 'is_all' => '1'] + $options);
                if($options['is_serial'] == 3 && (empty($serialBegin) || empty($serialEnd))){
                    $this->flashMessenger()->addWarningMessage('Kh??ng t??m th???y chu???i n??y!');
                }else{
                    if(empty($arrCodes)){
                        $data = [
                            'agent_id'=> null,
                            'exported_at' => null
                        ];
                        //echo $valuePost['codes']; die();
                        $result = $this->entityCodes->updatesRowAsCondition($this->defineCompanyId, ['data' => $data] + $options);
                        if($result > 0){
                            $this->entityBillDetails->deleteRow($valuePost['codes']);
                            $this->flashMessenger()->addSuccessMessage('X??a d??? li???u th??nh c??ng!');
                        }else{
                            if($options['is_serial'] == 3){
                                $this->flashMessenger()->addWarningMessage('Kh??ng t??m th???y chu???i n??y!');
                            }else{
                                $this->flashMessenger()->addWarningMessage('Kh??ng t??m th???y tem n??y!');
                            }
                        }
                    }
                    else{
                        if($options['is_serial'] == 3){
                            $this->flashMessenger()->addWarningMessage('Trong chu???i n??y c?? tem ch??a ???????c xu???t ho???c ???? ???????c x??a tr?????c ????!');
                        }else{
                            $this->flashMessenger()->addWarningMessage('Tem n??y ch??a ???????c xu???t!');
                        }
                    }
                    $form->get('codes')->setValue('');
                }
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }
        return new ViewModel([
            'form' =>  $form,
            'arrDetails' => $arrDetails,
            'optionProducts' => $optionProducts,
            'billId'=>$billId,
            'agentId'=>$agentId
        ]);
    }

    public function addAction(){
        $agentId = (int)$this->params()->fromRoute('agentid', 0);
        $billId =(int)$this->params()->fromRoute('id', 0);
        $this->layout()->setTemplate('iframe/layout');
        $request = $this->getRequest();
        $form = new AddForm($this->sessionContainer->id);
        $optionProducts = $this->entityProducts->fetchAllOptions01(['company_id' => $this->defineCompanyId]);
        $form->get('products_id')->setValueOptions( ['' => '------- Ch???n m???t s???n ph???m -------'] + $optionProducts );
        $arrCodes = [];
        
        if($request->isPost()){
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if($form->isValid()){
                $dataDetails=[
                    'quantity' => $valuePost['quantity']
                ];
                $options = [
                    'is_serial' => '1',
                    'value_begin' => null,
                    'value_end' => null
                ];
                $arrQrcodes = explode('=', $valuePost['codes']);
                if(isset($arrQrcodes[1])){
                    $options['value_begin'] = $arrQrcodes[1];
                }else{
                    $arrSerials = explode('-', $valuePost['codes']);
                    if(!isset($arrSerials[1])){
                        $options['is_serial'] = 2; 
                        $options['value_begin'] = trim($arrSerials[0]);
                    }else{
                        $options['is_serial'] = 3;
                        $options['value_begin'] = trim($arrSerials[0]);
                        $options['value_end'] = trim($arrSerials[1]);
                        $serialBegin = $this->entityCodes->fetchOneRowAsSerial($this->defineCompanyId, trim($arrSerials[0]));
                        $serialEnd = $this->entityCodes->fetchOneRowAsSerial($this->defineCompanyId,trim($arrSerials[1]));
                    }
                }
                // ki???m tra serial nh???p v??o c?? t???n t???i ko
                if($options['is_serial'] == 3 && (empty($serialBegin) || empty($serialEnd))){
                        $this->flashMessenger()->addWarningMessage('Kh??ng t??m th???y chu???i n??y!');
                }else{
                    $countRow = $this->entityCodes->fetchRowAsCondition($this->defineCompanyId,$options);//?????m s??? serial ???????c nh???p v??o
                    $seriandproduct = $this->entityCodes->fetchRowAsProduct($this->defineCompanyId,$options,$valuePost['products_id']);
                    if($countRow == (int)$dataDetails['quantity']  && $countRow > 0){
                        $arrCodes = $this->entityCodes->fetchAlls($this->defineCompanyId, ['export' => '1', 'is_all' => '1'] + $options);
                        if(empty($arrCodes)){
                            if($countRow == $seriandproduct){
                                $valuePost['exported_at'] = (isset($valuePost['exported_at']) && $valuePost['exported_at'] != "") ? $valuePost['exported_at'] : (date("d/m/Y H:i:s", time()));
                                $exportedAt = date_create_from_format('d/m/Y H:i:s', $valuePost['exported_at']);
                                $dataCodes = [
                                    'agent_id' => $agentId,
                                    'exported_at' => date_format($exportedAt, 'Y-m-d H:i:s')
                                ];
                                $this->entityCodes->updatesRowAsCondition($this->defineCompanyId, ['data' => $dataCodes] + $options);//update 
                                        $dataDetails = [
                                        'code_serial' => $valuePost['codes'],
                                        'bill_id' => $billId,
                                        'product_id' => $valuePost['products_id'],
                                        'company_id' => COMPANY_ID,
                                        'unit_price' => $valuePost['unit_price'],
                                        'quantity' => $valuePost['quantity'],
                                        'total_price' => $valuePost['unit_price']*$valuePost['quantity'],
                                    ];
                                $this->entityBillDetails->addRow($dataDetails);
                                $this->flashMessenger()->addSuccessMessage('Th??m d??? li???u th??nh c??ng!'); 
                                $form->get('codes')->setValue('');
                            }
                            elseif($seriandproduct == 0){
                                $this->flashMessenger()->addWarningMessage('S??? serial kh??ng kh???p v???i s???n ph???m ???? nh???p kho');
                            }else{
                                $this->flashMessenger()->addWarningMessage('S???n ph???m n??y c??n t???n kho ??t h??n s??? l?????ng nh???p v??o');
                            }
                        }else{
                            $serials = '';
                            $j=0;
                            foreach($arrCodes as $items){
                                if($j!=0){
                                    $serials .='-';
                                }
                                $serials .= $items['serial'];
                                $j++;
                            }
                            if($options['is_serial'] == 3){
                                $this->flashMessenger()->addWarningMessage("Trong chu???i n??y tem c?? s??? serial $serials ???? ???????c xu???t tr?????c ????!");
                            }else{
                                $this->flashMessenger()->addWarningMessage("Tem c?? s??? serial $serials ???? ???????c xu???t tr?????c ????!");
                            }
                        }
                        
                    }elseif($countRow != (int)$dataDetails['quantity'] && $countRow!=0){
                        $this->flashMessenger()->addWarningMessage("S??? l?????ng serial nh???p v??o ph???i b???ng m???c s??? l?????ng s???n ph???m");
                    }
                    // elseif($countRow != $seriandproduct){
                    //     $this->flashMessenger()->addWarningMessage('S???n ph???m n??y c??n t???n kho ??t h??n s??? l?????ng nh???p v??o');
                    // }
                    else{
                        $this->flashMessenger()->addWarningMessage('Kh??ng t??m th???y tem n??y!');
                        // if($options['is_serial'] == 3){
                        //     $this->flashMessenger()->addWarningMessage('Kh??ng t??m th???y chu???i n??y!');
                        // }else{
                        //     $this->flashMessenger()->addWarningMessage('Kh??ng t??m th???y tem n??y!');
                        // }
                    }
                }
                
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }

        return new ViewModel([
            'form' => $form,
            'userId'=> $this->sessionContainer->id
        ]);
    }
}