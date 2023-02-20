<?php
namespace Persons\Controller;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Persons\Form\LeaveApplies\AddForm;
use Persons\Form\LeaveApplies\DeleteForm;
use Persons\Form\LeaveApplies\SearchForm;
use Persons\Model\LeaveLists;
use Persons\Model\LeaveRequests;
use Persons\Model\Notifications;
use Persons\Model\Profiles;
use Settings\Model\Settings;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class LeaveAppliesController extends AdminCore{

    public $entitySettings;
    public $entityUsers;
    public $entityProfiles;
    public $entityLeaveLists;
    public $entityLeaveRequests;
    public $entityNotifications;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    Users $entityUsers, Profiles $entityProfiles, LeaveLists $entityLeaveLists, LeaveRequests $entityLeaveRequests,
    Notifications $entityNotifications){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityUsers = $entityUsers;
        $this->entityProfiles = $entityProfiles;
        $this->entityLeaveLists = $entityLeaveLists;
        $this->entityLeaveRequests = $entityLeaveRequests;
        $this->entityNotifications = $entityNotifications;
    }

    public function indexAction(){
        $userId = \Admin\Service\Authentication::getId();
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        $arrLeaveRequests = new Paginator(new ArrayAdapter($this->entityLeaveRequests->fetchAllByUserId($userId , $queries)));

        $leaves = $this->entityLeaveLists->fetchRowByUserId($userId);
        $leaveDayRemain = $leaves['old_year_leave'] + $leaves['total_leave'] - $leaves['leave_day_used'];
        // \Zend\Debug\Debug::dump($arrLeaveRequests); die();
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrLeaveRequests->setCurrentPageNumber($page);
        $arrLeaveRequests->setItemCountPerPage($perPage);
        $arrLeaveRequests->setPageRange($contentPaginator['page_range']);

        return new ViewModel([
            'arrLeaveRequests' => $arrLeaveRequests,
            'contentPaginator' => $contentPaginator,
            'optionProfileName' => $this->entityProfiles->fetchAllByName(), 
            'optionProfileStartDate' => $this->entityProfiles->fetchStartDateById(),
            'currentName' => $this->entityProfiles->fetchNameByUserId(),
            'formSearch' => $formSearch,
            'leaveDayRemain' => $leaveDayRemain,
        ]);
    }

    // public function addAction(){
    //     $this->layout()->setTemplate('empty/layout');
    //     $optionProfileId = $this->entityProfiles->fetchIdByUserId();
    //     $request = $this->getRequest();
    //     $form = new AddForm($request->getRequestUri());

    //     $optionsUserId = $this->entityUsers->fetchUserAsCompanies();
    //     $form->get('user_id')->setValueOptions([''=> '---Chọn nhân sự---'] + $optionsUserId);

    //     if($request->isPost()){
    //         $valuePost = $request->getPost()->toArray();
    //         // \Zend\Debug\Debug::dump($valuePost) ; die();
    //         $form->setData($valuePost);
    //         if($form->isValid()){
    //             $leaves = $this->entityLeaveLists->fetchRowByUserId($valuePost['user_id']);
    //             $leaveDayRemain = $leaves['old_year_leave'] + $leaves['total_leave'] - $leaves['leave_day_used'];

    //             $leaveStartDate = date_create_from_format('d/m/Y H:i:s', $valuePost['leave_start_date']);
    //             $strtotimeLeaveStartDate = strtotime(date_format($leaveStartDate, 'Y-m-d'));
                
    //             $leaveStopDate = (isset($valuePost['leave_stop_date']) && $valuePost['leave_stop_date'] != "") ? date_create_from_format('d/m/Y H:i:s', $valuePost['leave_stop_date']) : $leaveStartDate;
    //             $strtotimeLeaveStopDate = strtotime(date_format($leaveStopDate, 'Y-m-d'));

    //             $optionLeaveStartDate = ($valuePost['option_leave_start_date'] !=0 ) ? 0.5 : $valuePost['option_leave_start_date'];
    //             $optionLeaveStoptDate = ($valuePost['option_leave_stop_date'] !=0 ) ? 0.5 : $valuePost['option_leave_stop_date'];
                
    //             $totalApplyLeaves = (round(($strtotimeLeaveStopDate - $strtotimeLeaveStartDate)/(60*60*24)) + 1) - ($optionLeaveStartDate + $optionLeaveStoptDate);
                
    //             if($totalApplyLeaves > $leaveDayRemain){
    //                 $this->flashMessenger()->addWarningMessage('Số ngày nghỉ phép còn lại không đủ, hãy chọn lại!');
    //                 return new ViewModel(['form'=>$form]);
    //             }
    //             // \Zend\Debug\Debug::dump($totalApplyLeaves) ; die();
    //             if($totalApplyLeaves == 0){
    //                 $this->flashMessenger()->addWarningMessage('Số ngày nghỉ đã chọn là 0, hãy chọn lại!');
    //                 return new ViewModel(['form'=>$form]);
    //             }
    //             if($totalApplyLeaves < 0){
    //                 $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
    //                 return new ViewModel(['form'=>$form]);
    //             }
    //             $data = [
    //                 'user_id' => $valuePost['user_id'],
    //                 'profile_id' => $optionProfileId[$valuePost['user_id']],
    //                 'leave_start_date' => date_format($leaveStartDate, 'Y-m-d H:i:s'),
    //                 'leave_stop_date' => date_format($leaveStopDate, 'Y-m-d H:i:s'),
    //                 'total_apply_leave' => $totalApplyLeaves,
    //                 'option_leave_start_date' => $valuePost['option_leave_start_date'],
    //                 'option_leave_stop_date' => $valuePost['option_leave_stop_date'],
    //                 'annual_leave' => date_format(date_create(\Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()), "Y"),
    //                 'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
    //             ];
    //             // foreach($valuePost['send_to'] as $key => $item){
    //             //     $dataNotify[$key] = [
    //             //         "user_id_send" => $userId,
    //             //         "user_id_receive" => $item,
    //             //         "description" => "Đăng kí nghỉ phép",
    //             //         "url" => "/persons/leave-requests/index/",
    //             //         "created_at"    => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
    //             //     ];
    //             //     $this->entityNotifications->addRow($dataNotify[$key]);
    //             // }
    //             $this->entityLeaveRequests->addRow($data);
    //             $dataLeaves = [
    //                 'leave_day_used' => $leaves['leave_day_used'] + $totalApplyLeaves,
    //             ];
    //             // \Zend\Debug\Debug::dump($dataLeaves); die();
    //             $this->entityLeaveLists->updateRow($valuePost['user_id'], $dataLeaves);
    //             $this->flashMessenger()->addSuccessMessage('Thêm đề xuất thành công!');
    //             die('success');
    //         }else{
    //             $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
    //         }
    //     }
    //     return new ViewModel([
    //         'form' => $form
    //     ]);
    // }

    // public function leaveRemainAction(){
    //     $request = $this->getRequest();
    //     if($request->isPost()){
    //         $valuePost = $request->getPost()->toArray();
    //         if(isset($valuePost['user_id']) && $valuePost['user_id'] != ''){
    //             $leaves = $this->entityLeaveLists->fetchRowByUserId($valuePost['user_id']);
    //             $leaveDayRemain = $leaves['old_year_leave'] + $leaves['total_leave'] - $leaves['leave_day_used'];
    //             echo $leaveDayRemain; 
    //             die();
    //         }else{
    //             die();
    //         }
    //     }else{
    //         $this->flashMessenger()->addWarningMessage('Không đủ quyền truy cập, đề nghị liên hệ Admin để biết thêm chi tiết!');
	// 	    $this->redirect()->toRoute('admin/index');
    //     }
    //     die();
    // }

    // public function deleteAction(){
    //     $this->layout()->setTemplate('empty/layout');
    //     $id = $this->params()->fromRoute('id', 0);
    //     $userId = \Admin\Service\Authentication::getId();
    //     $valueCurrent = $this->entityLeaveRequests->fetchRowById($id);
    //     $valueCurrentLeave = $this->entityLeaveLists->fetchRowByUserId($userId);
    //     $dateTimeCurrent = \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent();
    //     // \Zend\Debug\Debug::dump($valueCurrent); die();
    //     if(empty($valueCurrent)){
    //         die('Not found!');
    //     }
    //     if($valueCurrent['status'] == 0 || $valueCurrent['status'] == -1){
    //         die('<h4><b>Phép này không được duyệt hoặc đã được hủy trước đó, không thể hủy!</h4></b>');
    //     }
    //     $request = $this->getRequest();
    //     $form = new DeleteForm($request->getRequestUri());
    //     if($request->isPost()){
    //         if($dateTimeCurrent >= date_format(date_create($valueCurrent['leave_start_date']),"Y-m-d 00:00:00")){
    //             $this->flashMessenger()->addWarningMessage('Đã qua ngày nghỉ, hủy phép không thành công!');
    //             die('success');
    //         }
    //         $dataLeaves = [
    //             'leave_day_used' => $valueCurrentLeave['leave_day_used'] - $valueCurrent['total_apply_leave']
    //         ];
    //         $this->entityLeaveRequests->updateRow($id, ['status'=> '-1']);
    //         $this->entityLeaveLists->updateRow($userId, $dataLeaves);
    //         $this->flashMessenger()->addSuccessMessage('Hủy phép thành công!');
    //         die('success');
    //     }
    //     return new ViewModel([
    //         'form' => $form
    //     ]);
    // }
}