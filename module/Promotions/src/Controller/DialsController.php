<?php
namespace Promotions\Controller;

use Zend\View\Model\ViewModel;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Promotions\Model\Dials;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Promotions\Form\Dials\AddForm;
use Promotions\Model\Promotions;
use Promotions\Model\DialsPromotions;
use Zend\Filter\File\Rename;
use Zend\File\Transfer\Adapter\Http;
use Zend\Filter\File\RenameUpload;
use Promotions\Form\Dials\EditForm;
use Promotions\Form\Dials\DeleteForm;
use Promotions\Model\Prizes;
use Promotions\Model\ListDials;
use Promotions\Model\WinnerDials;
use Promotions\Form\Dials\SearchForm;
use Settings\Model\LogSms;
use Promotions\Form\Dials\StatisticListForm;
use Promotions\Form\Dials\StatisticWinForm;
use Settings\Model\Companies;

class DialsController extends AdminCore
{
    
    private $entitySettings;
    private $entityDials;
    private $entityPromotions;
    private $entityDialsPromotions;
    private $entityPrizes;
    private $entityListDials;
    private $entityWinnerDials;
    private $entityLogSms;
    private $entityCompanies;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, Dials $entityDials, 
        Promotions $entityPromotions, DialsPromotions $entityDialsPromotions, Prizes $entityPrizes, ListDials $entityListDials,
        WinnerDials $entityWinnerDials, LogSms $entityLogSms, Companies $entityCompanies) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityDials = $entityDials;
        $this->entityPromotions = $entityPromotions;
        $this->entityDialsPromotions = $entityDialsPromotions;
        $this->entityPrizes = $entityPrizes;
        $this->entityListDials = $entityListDials;
        $this->entityWinnerDials = $entityWinnerDials;
        $this->entityLogSms = $entityLogSms;
        $this->entityCompanies = $entityCompanies;
    }
    
    public function indexAction(){
        $formSearch = new SearchForm('index', $this->sessionContainer->id);
        if($this->sessionContainer->id == '1'){
            $arrCompanies = $this->entityCompanies->fetchAllToOptions();
            $formSearch->get('company_id')->setValueOptions( [
                '' => '--- Ch???n m???t c??ng ty ---'] + 
                $arrCompanies 
            );
        }
        
        $queries = $this->params()->fromQuery();
        // reset company_id if empty
        if(!isset($queries['company_id']) || $queries['company_id'] == ''){
            $queries['company_id'] = isset($queries['company_id']) ? $queries['company_id'] : $this->defineCompanyId;
        }
        $formSearch->setData($queries);
        
        $arrDials = new Paginator(new ArrayAdapter( $this->entityDials->fetchAlls($queries) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrDials->setCurrentPageNumber($page);
        $arrDials->setItemCountPerPage($perPage);
        $arrDials->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrDials' => $arrDials, 
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'userId' => $this->sessionContainer->id,
            'optionCompanies' => $this->entityCompanies->fetchAllToOptions()
        ]);
    }
    
    public function addAction(){
        if($this->defineCompanyId == null){
            $this->flashMessenger()->addWarningMessage('T??i kho???n n??y kh??ng c?? quy???n t???i ????y!');
            return $this->redirect()->toRoute('promotions/dials');
        }
        $view = new ViewModel();
        $form = new AddForm();
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($valuePost);
            if($form->isValid()){
                $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_begin']);
                $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_end']);
                $data = [
                    'company_id' => $this->defineCompanyId,
                    'name' => $valuePost['name'],
                    'datetime_begin' => date_format($datetimeBegin, 'Y-m-d H:i:s'),
                    'datetime_end' => date_format($datetimeEnd, 'Y-m-d H:i:s'),
                    'description' => $valuePost['description'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                    'win_more' => $valuePost['win_more'],
                    'status' => $valuePost['status']
                ];
                $file = $request->getFiles()->toArray();
                //upload background
                if($file['background']['name'] != ''){
                    $data['background'] = $this->uploadFile($file, 'background');
                }
                // upload music_background
                if($file['music_background']['name'] != ''){
                    $data['music_background'] = $this->uploadFile($file, 'music_background');
                }
                // upload music_dial
                if($file['music_dial']['name'] != ''){
                    $data['music_dial'] = $this->uploadFile($file, 'music_dial');
                }
                // upload music_win
                if($file['music_win']['name'] != ''){
                    $data['music_win'] = $this->uploadFile($file, 'music_win');
                }
                $dialId = $this->entityDials->addRow($data);
                if(!empty($valuePost['promotions_id'])){
                    foreach($valuePost['promotions_id'] as $item){
                        $dataDialsPromotions = [
                            'dial_id' => $dialId,
                            'promotion_id' => $item,
                            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                        ];
                        $this->entityDialsPromotions->addRow($dataDialsPromotions);
                    }
                }
                $this->flashMessenger()->addSuccessMessage('Th??m d??? li???u th??nh c??ng!');
                return $this->redirect()->toRoute('promotions/dials');
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }
        $arrPromotions = $this->entityPromotions->fetchAllOptions01(['dial' => '1', 'company_id' => $this->defineCompanyId]);
        
        $view->setVariable('arrPromotions', $arrPromotions);
        $view->setVariable('form', $form);
        return $view;
    }
    
    public function editAction(){
        if($this->defineCompanyId == null){
            $this->flashMessenger()->addWarningMessage('T??i kho???n n??y kh??ng c?? quy???n t???i ????y!');
            return $this->redirect()->toRoute('promotions/dials');
        }
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityDials->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('promotions/dials');
        }else{
            $valuePost = $valueCurrent;
        }
        
        $form = new EditForm();
        
        $request = $this->getRequest();
        if($request->isPost()){
            $valuePost = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($valuePost);
            if($form->isValid()){
                $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_begin']);
                $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_end']);
                $data = [
                    'name' => $valuePost['name'],
                    'datetime_begin' => date_format($datetimeBegin, 'Y-m-d H:i:s'),
                    'datetime_end' => date_format($datetimeEnd, 'Y-m-d H:i:s'),
                    'description' => $valuePost['description'],
                    'win_more' => $valuePost['win_more'],
                    'status' => $valuePost['status']
                ];
                $file = $request->getFiles()->toArray();
                //upload background
                if($file['background']['name'] != ''){
                    $data['background'] = $this->uploadFile($file, 'background');
                }
                // upload music_background
                if($file['music_background']['name'] != ''){
                    $data['music_background'] = $this->uploadFile($file, 'music_background');
                }
                // upload music_dial
                if($file['music_dial']['name'] != ''){
                    $data['music_dial'] = $this->uploadFile($file, 'music_dial');
                }
                // upload music_win
                if($file['music_win']['name'] != ''){
                    $data['music_win'] = $this->uploadFile($file, 'music_win');
                }
                $this->entityDials->updateRow($id, $data);
                
                // update promotionsProducts
                $this->entityDialsPromotions->deleteRowsAsDialId($id);
                if(!empty($valuePost['promotions_id'])){
                    foreach($valuePost['promotions_id'] as $item){
                        $dataDialsPromotions = [
                            'dial_id' => $id,
                            'promotion_id' => $item,
                            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                        ];
                        $this->entityDialsPromotions->addRow($dataDialsPromotions);
                    }
                }
                $this->flashMessenger()->addSuccessMessage('S???a d??? li???u th??nh c??ng!');
                $valuePost['background'] = isset($data['background']) ? $data['background'] : $valueCurrent['background'];
                $valuePost['music_background'] = isset($data['music_background']) ? $data['music_background'] : $valueCurrent['music_background'];
                $valuePost['music_dial'] = isset($data['music_dial']) ? $data['music_dial'] : $valueCurrent['music_dial'];
                $valuePost['music_win'] = isset($data['music_win']) ? $data['music_win'] : $valueCurrent['music_win'];
            }else{
                $this->flashMessenger()->addWarningMessage('L???i nh???p d??? li???u, ????? ngh??? ki???m tra l???i!');
            }
        }else{
            $valuePost['datetime_begin'] = date_format(date_create($valuePost['datetime_begin']), 'd/m/Y H:i:s');
            $valuePost['datetime_end'] = date_format(date_create($valuePost['datetime_end']), 'd/m/Y H:i:s');
        }
        $form->setData($valuePost);
        $arrDialsPromotions = $this->entityDialsPromotions->fetchAllAsDialId($id, true);
        $arrPromotions = $this->entityPromotions->fetchAllOptions01(['dial' => '1', 'company_id' => $this->defineCompanyId]);
        
        return new ViewModel([
            'form' => $form,
            'valuePost' => $valuePost,
            'arrDialsPromotions' => $arrDialsPromotions,
            'arrPromotions' => $arrPromotions
        ]);
    }
    
    private function uploadFile($file = null, $param = null){
        if($file == null || $param == null) return '';
        $info = pathinfo($file[$param]['name']);
        $url = 'promotions/' . $info['filename'] . '_' . time() . '.' . $info['extension'];
        $fileUpload = new RenameUpload([
            'target' => FILES_UPLOAD . $url,
            'randomize' => false
        ]);
        $fileUpload->filter($file[$param]);
        return $url;
    }
    
    public function deleteAction(){
        if($this->defineCompanyId == null){
            $this->flashMessenger()->addWarningMessage('T??i kho???n n??y kh??ng c?? quy???n t???i ????y!');
            return $this->redirect()->toRoute('promotions/dials');
        }
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityDials->fetchRow($id);
        if(empty($valueCurrent)){
            $this->redirect()->toRoute('promotions/dials');
        }
        $form = new DeleteForm($request->getRequestUri());
        
        // check relationship
        $checkRelationship = [];
//         $countDialsPromotions = $this->entityDialsPromotions->fetchCount(['dial_id' => $id]);
//         $countPrizes = $this->entityPrizes->fetchCount(['dial_id' => $id]);
//         if($countDialsPromotions > 0){
//             $checkRelationship['dials_promotions'] = 1;
//         }
//         if($countPrizes > 0){
//             $checkRelationship['prizes'] = 1;
//         }
        
        if($request->isPost()){
            // delete all dials promotions
//             $this->entityDialsPromotions->deleteRows(['dial_id' => $id]);
            // delete all winner dials as prizes
//             $arrPrizes = $this->entityPrizes->fetchAllConditions(['dial_id' => $id]);
//             if(!empty($arrPrizes)){
//                 foreach($arrPrizes as $item){
//                     $this->entityWinnerDials->deleteRows(['prize_id' => $item['id']]);
//                 }
//             }
            // delete all prizes
//             $this->entityPrizes->deleteRows(['dial_id' => $id]);
            // delete promotion
            $this->entityDials->updateRow($id, ['status' => '-1']);
            $this->flashMessenger()->addSuccessMessage('X??a d??? li???u th??nh c??ng!');
            die('success');
        }
        
        return new ViewModel([
            'form' => $form, 
            'checkRelationship' => $checkRelationship, 
            'valueCurrent' => $valueCurrent
        ]);
    }
    
    public function iframeAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityDials->fetchRow($id);
        //\Zend\Debug\Debug::dump($valueCurrent); die();
        if(empty($valueCurrent)){
            die('success');
        }
        $view->setVariable('id', $id);
        return $view;
    }
    
    public function playAction(){
        $view = new ViewModel();
        $this->layout()->setTemplate('dial/layout');
        $request = $this->getRequest();
        
        $id = (int)$this->params()->fromRoute('id', 0);
        // echo $id; die();
        $valueCurrent = $this->entityDials->fetchRow($id);
        if(empty($valueCurrent)){
            $view->setVariable('error', 'Kh??ng t??m th???y ch????ng tr??nh n??y, ????? ngh??? li??n h??? qu???n l?? ????? ki???m tra chi ti???t!');
        }else{
            $view->setVariable('valueCurrent', $valueCurrent);
            // get list prizes
            $arrPrizes = $this->entityPrizes->fetchAllOptions03(['is_remain' => true, 'dial_id' => $valueCurrent['id'], 'key_is_id' => '1']);
            $view->setVariable('arrPrizes', $arrPrizes);
            // $strPrizes = '';
            $strPrizes = "'" . implode("','", $this->entityPrizes->fetchAllOptions02(['dial_id' => $valueCurrent['id']])) . "'";
            if(empty($arrPrizes)){
                $view->setVariable('error', '???? h???t gi???i th?????ng ????? quay s???');
            }else{
                // get list prizes
                // $strPrizes = "'" . implode("','", $this->entityPrizes->fetchAllOptions02(['dial_id' => $valueCurrent['id']])) . "'";
                //get all promotions as dials_id
                $arrDialsPromomtions = $this->entityDialsPromotions->fetchAllAsDialId($id, true);
                $strDialsPromotions = "'" . implode("','", $arrDialsPromomtions) . "'";
                // check data not empty
                $arrListDials = $this->entityListDials->fetchRowWinner($strDialsPromotions, 1, $valueCurrent['datetime_begin'], $valueCurrent['datetime_end'], $valueCurrent['win_more'], $strPrizes);
                // \Zend\Debug\Debug::dump($arrListDials); die();
                if(empty($arrListDials)){
                    $view->setVariable('error', '???? h???t danh s??ch tr??ng th?????ng!');
                }
                //die('abc');
            }

            // get winner dials
            $arrWinnerDials = $this->entityWinnerDials->fetchAlls(['strPrizes' => $strPrizes]);
            $view->setVariable('arrWinnerDials', $arrWinnerDials);
        }
        return $view;
    }
    
    public function addWinnerAction(){
        $params = $this->getRequest()->getPost()->toArray();
        
        $id = $params['id'];
        $prizeId = $params['cbo_prizes_id'];
        $limitDial = $params['limit_dial'];
        
        $dialCurrent = $this->entityDials->fetchRow($id);
        if(empty($dialCurrent)){
            echo json_encode([
                'error' => 'Kh??ng t??m th???y ch????ng tr??nh quay s??? n??y!'
            ]);
            die();
        }
        
        $prizeCurrent = $this->entityPrizes->fetchRow($prizeId);
        if(empty($prizeCurrent)){
            echo json_encode([
                'error' => 'Kh??ng t??m th???y gi???i th?????ng n??y!'
            ]);
            die();
        }
        $dataPrize = [
            'number_win' => $prizeCurrent['number_win'],
            'number_won' => $prizeCurrent['number_won']
        ];
        // re check limitDial
        if($limitDial > $prizeCurrent['number_win']){
            $limitDial = $prizeCurrent['number_win'];
        }
        
        //get all promotions as dials_id
        $arrDialsPromomtions = $this->entityDialsPromotions->fetchAllAsDialId($id, true);
        $strDialsPromotions = "'" . implode("','", $arrDialsPromomtions) . "'";
        // get code and phone win
        $strPrizes = "'" . implode("','", $this->entityPrizes->fetchAllOptions02(['is_remain' => true, 'dial_id' => $dialCurrent['id'], 'key_is_id' => '1'])) . "'";
        $arrListDials = $this->entityListDials->fetchRowWinner($strDialsPromotions, $limitDial, $dialCurrent['datetime_begin'], $dialCurrent['datetime_end'], $dialCurrent['win_more'], $strPrizes);
        
        if(empty($arrListDials)){
            echo json_encode([
                'error' => 'Danh s??ch quay s??? ???? h???t!'
            ]);
            die();
        }

        $phones = '';
        $codes = '';
        $winner = [];
        $i = 0;
        foreach($arrListDials as $item){
            // add winner 
            $data = [
                'company_id' => $this->defineCompanyId,
                'prize_id' => $prizeCurrent['id'],
                'list_dial_id' => $item['id'],
                'code_id' => $item['code_id'],
                'phone_id' => $item['phone_id'],
                'message' => $prizeCurrent['message'],
                'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
            ];
            $winnderDialId = $this->entityWinnerDials->addRow($data);
            // update number in prize
            $dataPrize['number_win'] = (int)$dataPrize['number_win'] - 1;
            $dataPrize['number_won'] = (int)$dataPrize['number_won'] + 1;
            $this->entityPrizes->updateRow($prizeCurrent['id'], $dataPrize);
            if($phones == ''){
                $phones = $item['phone_id'];
                $codes = $item['code_id'];
            }else{
                $phones .= ',' . $item['phone_id'];
                $codes .= ',' . $item['code_id'];
            }
            $winner[$i] = [
                    'id' => $winnderDialId,
                    'name' => $prizeCurrent['name'],
                    'phone' => $item['phone_id'],
                    'code' => $item['code_id']
            ];
//             if($prizeCurrent['price_topup'] != 0){
//                 // return topup to phone
//                 $this->topupBluesea(array(
//                     "phone_id" => $item['phone_id'], 
//                     "price_topup" => $prizeCurrent['price_topup'], 
//                     "codes_id" => $item['codes_id'], 
//                     "message" => $prizeCurrent['message'],
//                 ));
//             }else{
                // return message to phone
//                 $this->sendSmsBluesea(array(
//                     "phone_id" => $item['phone_id'],
//                     "code_id" => $item['code_id'],
//                     "message" => $prizeCurrent['message']
//                 ));
//             }
            $i++;
        }
        
        $data = [
            'error' => '',
            'phones' => $phones,
            'codes' => $codes,
            'winner' => $winner
        ];
        echo json_encode($data);
        die();
    }
    
    public function deleteWinnerAction(){
        $id = (int)$this->params()->fromRoute('id', 0);
        $winnerDialId = (int)$this->params()->fromRoute('winner_dial_id', 0);
        $dialCurrent = $this->entityDials->fetchRow($id);
        if(empty($dialCurrent)){
            echo json_encode([
                'error' => 'Kh??ng t??m th???y ch????ng tr??nh quay s??? n??y!'
            ]);
            die();
        }
        
        $winnerDialCurrent = $this->entityWinnerDials->fetchRow($winnerDialId);
        if(empty($winnerDialCurrent)){
            echo json_encode([
                'error' => 'Kh??ng t??m th???y s??t tr??ng th?????ng n??y!'
            ]);
            die();
        }
        
        $this->entityWinnerDials->deleteRow($winnerDialId);
        echo json_encode([
            'message' => 'X??a th??nh c??ng!'
        ]);
        die();
    }
    
    // https://packagist.org/packages/zendframework/zend-soap
    public function sendSmsBluesea($options = null){
        if($options == null) return false;
        if($options['phones_id'] == '') return false;
        if($options['codes_id'] == '') return false;
        if($options['message'] == '') return false;
        
        $phoneId = $options['phones_id'];
        $codeId = $options['codes_id'];
        $message = \Pxt\String\ChangeString::deleteAccented($options['message']);
        
        $client = new \Zend\Soap\Client('http://sms.8x77.vn:8077/mt-services/MTService?wsdl', ['soap_version' => SOAP_1_1]);
        //$client = new \SoapClient("http://sms.8x77.vn:8077/mt-services/MTService?wsdl", true);
        $client->setHttpLogin('brandandpro');
        $client->setHttpPassword('andpro!678');
        //$client->setCredentials('brandandpro', 'andpro!678');
        $arr = [
            "string"	=> $phoneId,
            "string0"	=> base64_encode($message),
            "string1"	=> "ANDPRO",
            "string2"	=> "ANDPRO",
            "string3"	=> 0,
            "string4"	=> 0,
            "string5"	=> "1",
            "string6"	=> "1",
            "string7"	=> "0",
            "string8"	=> "0"
        ];
        
        $result = $client->call("sendMT", $arr);

        //echo $result; die();
        $data = array(
            "phones_id" => $phoneId,
            "codes_id" => $codeId,
            "is_sms" => 1,
            "content_in" => json_encode($options),
            "content_out" => $result,
            "created_at" => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
        );
        $this->entityLogSms->addRow($data);
    }
	
	public function topupBluesea($options = null){
        if($options == null) return false;
        if($options['phones_id'] == '') return false;
        if($options['codes_id'] == '') return false;
        if($options['price_topup'] == '' || $options['price_topup'] == '0') return false;
        
		$priceTopup = $options["price_topup"];
		$phoneId = $options["phones_id"];
		$codeId = $options["codes_id"];
		$mode = '1';
		// VT ~ Viettel; VP ~ Vinaphone; MB ~ Mobiphone; VNM ~ Vietnamobile; GTEL ~ G-Mobile
		$productcode = "";
		$time = time();
		$local = date("YmdHis", time());
		$abc = 0;
		// L???y t??n m???ng v?? productcode
		$nameMang = "";
		$arrViettel = array('8498','8497','8496','84169','8439','84168','8438','84167','8437','84166','8436','84165','8435','84164','8434','84163','8433','84162','8432','8486');
		$arrVinaphone = array('8491','8494','84123','8483','84124','8484','84125','8485','84127','8481','84129','8482','8488');
		$arrMobiphone = array('8490','8493','84120','8470','84121','8479','84122','8477','84126','8476','84128','8478','8489');
		$arrVietnaMobile = array('8492','84188','8458');
		$arrSFone = array('8495');
		$arrGFone = array('84993','84994','84995','84996','8499');
		
		if(in_array(substr($phoneId, 0, 4), $arrViettel) || in_array(substr($phoneId, 0, 5), $arrViettel)){ 
		    $nameMang = " VIETTEL"; 
		    $productcode = "VIETTEL"; 
		}
		if(in_array(substr($phoneId, 0, 4), $arrVinaphone) || in_array(substr($phoneId, 0, 5), $arrVinaphone)){ 
		    $nameMang = " Vinaphone"; 
		    $productcode = "VNP";
		}
		if(in_array(substr($phoneId, 0, 4), $arrMobiphone) || in_array(substr($phoneId, 0, 5), $arrMobiphone)){ 
		    $nameMang = " Mobiphone"; 
		    $productcode = "VMS";
		}
		if(in_array(substr($phoneId, 0, 4), $arrVietnaMobile) || in_array(substr($phoneId, 0, 5), $arrVietnaMobile)){ 
		    /*$nameMang = " VietnaMobile"; $productcode = "VNM";*/ 
		    $nameMang = " VIETTEL"; 
		    $productcode = "VIETTEL"; 
		    $phoneId = "84962703188"; 
		    $abc = 1; 
		}
		if(in_array(substr($phoneId, 0, 4), $arrSFone) || in_array(substr($phoneId, 0, 5), $arrSFone)){
		    /*$nameMang = " SFone"; $productcode = "";*/ 
		    $nameMang = " VIETTEL"; 
		    $productcode = "VIETTEL"; 
		    $phoneId = "84962703188"; 
		    $abc = 1;
		}
		if(in_array(substr($phoneId, 0, 4), $arrGFone) || in_array(substr($phoneId, 0, 5), $arrGFone)){
		    /*$nameMang = " GFone"; $productcode = "GTEL";*/ 
		    $nameMang = " VIETTEL"; 
		    $productcode = "VIETTEL"; 
		    $phoneId = "84962703188"; 
		    $abc = 1; 
		}
		if($productcode == "" || $productcode == "GTEL"){
		    $nameMang = " VIETTEL"; 
		    $productcode = "VIETTEL"; 
		    $phoneId = "84962703188"; 
		    $abc = 1; 
		}
		// L???y th??? c??o
		$client = new \Zend\Soap\Client("http://sms.bluesea.vn:8077/Card/TopUpCard?wsdl", ['soap_version' => SOAP_1_1]);
		$arr = [
				  "User_ID" => $phoneId,
				  "Amount" => $priceTopup,
				  "mode"  => $mode,
				  "provider" => $productcode,
				  "merchant_code" => "ANDPRO"
		];
		$results = (array) $client->call("TopUpCard", [$arr]);
		if(isset($results["return"])){
			$input = $results["return"]; 
			$result = $this->Decrypt($input, "ANDPRO@20180614");
			$arrResults = explode("|", $result);
			if(isset($arrResults[1]) && $arrResults[0] == '0' && $mode == 0){
				$arrResult = explode(",", $arrResults[2]);
				// L???y m???ng gi?? tr??? th??? v?? truy???n ra ngo??i b???ng brandname
				$message = (isset($options['message']) && $options['message'] != '') ? $options['message'] : 'Ban da nhan duoc 1 the cao tri gia [giatri]d, ma nap the la: [mathe]. Df';
				$message = str_replace("[mathe]", $arrResult[1] , $message);
				$message = str_replace("[mapin]", $codeId, $message);
				$message = str_replace("[giatri]", number_format($priceTopup, 0, ',', '.') , $message);
				$options['message'] = $message;
				$this->sendSmsBluesea(array(
					"phones_id" => $phoneId, 
					"codes_id" => $codeId,
					"message" => $message,
				));
			}elseif($options['message'] != '' && $arrResults[0] == '0'){
			    $message = $options['message'];
				$this->sendSmsBluesea(array(
					"phones_id" => $phoneId, 
					"codes_id" => $codeId,
					"message" => $message,
				));
			}
			$data = array(
                "phones_id" => $phoneId,
                "codes_id" => $codeId,
                "is_sms" => 0,
                "content_in" => json_encode($options),
                "content_out" => json_encode($results + array("result" => $result)),
                "created_at" => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
			);
		}else{
			$data = array(
                "phones_id" => $phoneId,
                "codes_id" => $codeId,
                "is_sms" => 0,
                "content_in" => json_encode($options),
                "content_out" => json_encode($results),
                "created_at" => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
			);
		}
        $this->entityLogSms->addRow($data);
	}
	
	function Encrypt($input, $key_seed){
		 $input = trim($input);
		 $block = mcrypt_get_block_size('tripledes', 'ecb');
		 $len = strlen($input);
		 $padding = $block - ($len % $block);
		 $input .= str_repeat(chr($padding),$padding);
		// generate a 24 byte key from the md5 of the seed
		 $key = substr(md5($key_seed),0,24);
		 $iv_size = mcrypt_get_iv_size(MCRYPT_TRIPLEDES, MCRYPT_MODE_ECB);
		 $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		 // encrypt
		 $encrypted_data = mcrypt_encrypt(MCRYPT_TRIPLEDES, $key, $input, MCRYPT_MODE_ECB, $iv);
		 // clean up output and return base64 encoded
		  return base64_encode($encrypted_data);
	} //end function Encrypt()
	
	function Decrypt($input, $key_seed){
		$input = base64_decode($input);
		$key = substr(md5($key_seed),0,24);
		$text=mcrypt_decrypt(MCRYPT_TRIPLEDES, $key, $input, MCRYPT_MODE_ECB,'12345678');
		$block = mcrypt_get_block_size('tripledes', 'ecb');
		$packing = ord($text{strlen($text) - 1});
		if($packing and ($packing < $block)){
			for($P = strlen($text) - 1; $P >= strlen($text) - $packing; $P--){
				if(ord($text{$P}) != $packing){
					$packing = 0;
				}
			}
		}
		$text = substr($text,0,strlen($text) - $packing);
		return $text;
	}
    
    public function statisticListAction(){
        $formSearch = new StatisticListForm();
        $optionDials = $this->entityDials->fetchAllOptions01(['company_id' => $this->defineCompanyId]);
        $formSearch->get('dials_id')->setValueOptions( ['' => '------- Ch???n m???t ch????ng tr??nh quay s??? -------'] + $optionDials );
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        $queries["company_id"] = $this->defineCompanyId;
        if(isset($queries['btnExport'])){
            $this->statisticListExcel($queries);
        }
        
        $arrListDials = new Paginator(new ArrayAdapter( $this->entityListDials->fetchAlls($queries) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrListDials->setCurrentPageNumber($page);
        $arrListDials->setItemCountPerPage($perPage);
        $arrListDials->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrListDials' => $arrListDials, 
            'contentPaginator' => $contentPaginator,
            'optionDials' => $optionDials,
            'optionPromotions' => $this->entityPromotions->fetchAllOptions01(['company_id' => $this->defineCompanyId, 'dial' => 1]),
            'formSearch' => $formSearch,
            'queries' => $queries
        ]);
    }

    public function statisticListExcel($queries = null){
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        // get data
        $arrListDials = $this->entityListDials->fetchAlls($queries);
        if(empty($arrListDials)) return true;
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('C?? l???i trong qu?? tr??nh xu???t d??? li???u, ????? ngh??? li??n h??? Admin!');
            return $this->redirect()->toRoute('statistics/dials');
        }
        // ?????t t??n file
        $path = "Thong-ke-tham-gia-quay-so-" . date('d-m-Y-H-i-s') . ".xlsx";
        	
        /* T???o m???i m???t ?????i t?????ng PHPExcel */
        $objPHPExcel = new \PHPExcel();
        /* C??i ?????t Properties */
        $objPHPExcel->getProperties()->setCreator("Pxt Creator")
        ->setLastModifiedBy("Pxt Modified")
        ->setTitle("Pxt Title")
        ->setSubject("Pxt Subject")
        ->setDescription("Pxt Description")
        ->setKeywords("Pxt Keywords")
        ->setCategory("Pxt Category");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')
        ->setSize(12); /* C??i ?????t font cho c??? file */
        	
        /* C??i ?????t chi???u r???ng cho t???ng ?? */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        	
        /* Ch???nh d??ng ?????u ti??n */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:E1'); /* Nh??m c???t */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Th???ng k?? danh s??ch tham gia quay s???");
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_DARKGREEN ) );
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'STT')
        ->setCellValue('B2', 'M?? PIN')
        ->setCellValue('C2', 'S??? ??i???n tho???i')
        ->setCellValue('D2', 'Ch????ng tr??nh khuy???n m??i')
        ->setCellValue('E2', 'Ng??y tham gia');
        $objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getFont()->setBold(true);
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        // Truy???n d??? li???u v??o file
        $i = 3; // s??? b???t ?????u
        $j = 1;
        $optionPromotions = $this->entityPromotions->fetchAllOptions01(['company_id' => $this->defineCompanyId, 'dial' => 1]);
        //\Zend\Debug\Debug::dump($optionPromotions); die();
        foreach($arrListDials as $item){
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A" . $i, $j)
            ->setCellValue("B" . $i, $item['code_id'])
            ->setCellValue("C" . $i, $item['phone_id'])
            ->setCellValue("D" . $i, $optionPromotions[$item['promotion_id']])
            ->setCellValue("E" . $i, "'" . date_format(date_create($item['created_at']), 'd/m/Y H:i:s'));
            $i++;
            $j++;
        }
        unset($arrMessage);
        // Redirect output to a client???s web browser (Excel2007)
        ob_end_clean(); /* Xo?? (hay lo???i b???) b???t k??? kho???ng tr???ng trong c???p l???nh n??y*/
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$path.'"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    
        ob_end_clean();
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        return true;
    }
    
    public function statisticWinAction(){
        $formSearch = new StatisticWinForm();
        $optionDials = $this->entityDials->fetchAllOptions01(['company_id' => $this->defineCompanyId]);
        $formSearch->get('dials_id')->setValueOptions( ['' => '------- Ch???n m???t ch????ng tr??nh quay s??? -------'] + $optionDials );
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        if(isset($queries['btnExport'])){
            $this->statisticWinExcel($queries);
        }
        $arrWinnerDials = new Paginator(new ArrayAdapter( $this->entityWinnerDials->fetchAlls($queries) ));
        // get setting paginator
        $settingPaginator = $this->entitySettings->fetchRow('paginator');
        $contentPaginator = json_decode($settingPaginator['content'], true);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrWinnerDials->setCurrentPageNumber($page);
        $arrWinnerDials->setItemCountPerPage($perPage);
        $arrWinnerDials->setPageRange($contentPaginator['page_range']);
//         \Zend\Debug\Debug::dump($optionDials);
//         \Zend\Debug\Debug::dump($this->entityPrizes->fetchAllOptions03(['company_id' => $this->defineCompanyId]));
//         die();
        return new ViewModel([
            'arrWinnerDials' => $arrWinnerDials, 
            'contentPaginator' => $contentPaginator,
            'optionPrizes' => $this->entityPrizes->fetchAllOptions03(['company_id' => $this->defineCompanyId]),
            'optionDials' => $optionDials,
            'formSearch' => $formSearch,
            'queries' => $queries
        ]);
    }

    public function statisticWinExcel($queries = null){
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        // get data
        $arrWinnerDials = $this->entityWinnerDials->fetchAllConditions($queries);
        if(empty($arrWinnerDials)) return true;
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        }
        else{
            $this->flashMessenger()->addWarningMessage('C?? l???i trong qu?? tr??nh xu???t d??? li???u, ????? ngh??? li??n h??? Admin!');
            return $this->redirect()->toRoute('statistics/dials', ['action' => 'winner']);
        }
        // ?????t t??n file
        $path = "Thong-ke-trung-thuong-quay-so-" . date('d-m-Y-H-i-s') . ".xlsx";
        	
        /* T???o m???i m???t ?????i t?????ng PHPExcel */
        $objPHPExcel = new \PHPExcel();
        /* C??i ?????t Properties */
        $objPHPExcel->getProperties()->setCreator("Pxt Creator")
        ->setLastModifiedBy("Pxt Modified")
        ->setTitle("Pxt Title")
        ->setSubject("Pxt Subject")
        ->setDescription("Pxt Description")
        ->setKeywords("Pxt Keywords")
        ->setCategory("Pxt Category");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')
        ->setSize(12); /* C??i ?????t font cho c??? file */
        	
        /* C??i ?????t chi???u r???ng cho t???ng ?? */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(100);
        	
        /* Ch???nh d??ng ?????u ti??n */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:G1'); /* Nh??m c???t */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Th???ng k?? danh s??ch tr??ng th?????ng quay s???");
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_DARKGREEN ) );
        
        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'STT')
        ->setCellValue('B2', 'M?? tr??ng th?????ng')
        ->setCellValue('C2', 'S??? ??i???n tho???i')
        ->setCellValue('D2', 'Ch????ng tr??nh quay s???')
        ->setCellValue('E2', 'Gi???i th?????ng')
        ->setCellValue('F2', 'Ng??y tr??ng th?????ng')
        ->setCellValue('G2', 'Tin nh???n tr??ng th?????ng');
        $objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getFont()->setBold(true);
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        // Truy???n d??? li???u v??o file
        $i = 3; // s??? b???t ?????u
        $j = 1;
        $optionPrizes = $this->entityPrizes->fetchAllConditions(['key_is_id' => '1']);
        $optionDials = $this->entityDials->fetchAllConditions(['re_options' => ['key' => '[id]', 'value' => '[name]']]);
        //\Zend\Debug\Debug::dump($optionPromotions); die();
        foreach($arrWinnerDials as $item){
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A" . $i, $j)
            ->setCellValue("B" . $i, $item['codes_id'])
            ->setCellValue("C" . $i, $item['phones_id'])
            ->setCellValue("D" . $i, (isset($optionPrizes[$item['prizes_id']]['dials_id']) ? $optionDials[$optionPrizes[$item['prizes_id']]['dials_id']] : ''))
            ->setCellValue("E" . $i, (isset($optionPrizes[$item['prizes_id']]['name']) ? $optionPrizes[$item['prizes_id']]['name'] : ''))
            ->setCellValue("F" . $i, "'" . date_format(date_create($item['created_at']), 'd/m/Y H:i:s'))
            ->setCellValue("G" . $i, $item['message']);
            $i++;
            $j++;
        }
        unset($arrWinnerDials);
        // Redirect output to a client???s web browser (Excel2007)
        ob_end_clean(); /* Xo?? (hay lo???i b???) b???t k??? kho???ng tr???ng trong c???p l???nh n??y*/
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$path.'"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    
        ob_end_clean();
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        return true;
    }
}
