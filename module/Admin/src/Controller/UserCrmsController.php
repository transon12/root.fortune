<?php

namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Admin\Form\UserCrms\AddForm;
use Admin\Form\UserCrms\EditForm;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Admin\Form\UserCrms\DeleteForm;
use Settings\Model\Settings;
use Admin\Form\UserCrms\SearchForm;
use Admin\Model\UserCrms;
use Admin\Model\Users;

class UserCrmsController extends AdminCore
{

    public $entityUserCrms;
    public $entityUsers;
    public $entitySettings;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, UserCrms $entityUserCrms, Users $entityUsers)
    {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings   = $entitySettings;
        $this->entityUserCrms   = $entityUserCrms;
        $this->entityUsers      = $entityUsers;
    }

    public function indexAction()
    {
        $formSearch = new SearchForm('index', $this->sessionContainer->id);

        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);

        $arrUserCrms = new Paginator(new ArrayAdapter($this->entityUserCrms->fetchAlls($queries)));

        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        //\Zend\Debug\Debug::dump($contentPaginator); die();
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;

        $arrUserCrms->setCurrentPageNumber($page);
        $arrUserCrms->setItemCountPerPage($perPage);
        $arrUserCrms->setPageRange($contentPaginator['page_range']);

        return new ViewModel([
            'arrUserCrms' => $arrUserCrms,
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'optionUsers' => $this->entityUsers->fetchAllOptions()
        ]);
    }

    public function addAction()
    {
        $view = new ViewModel();
        $form = new AddForm('add');

        $optionUsers = $this->entityUsers->fetchAllOptions();
        $form->get('user_id')->setValueOptions(
            ['' => '--- Chọn một tài khoản fortune ---'] + $optionUsers
        );

        $request = $this->getRequest();
        if ($request->isPost()) {
            $valuePost = $request->getPost()->toArray();
            //$valuePost['company_id'] = null;
            $form->setData($valuePost);
            $isValid = $form->isValid();
            if ($this->entityUserCrms->fetchRowAsId($valuePost['id'])) {
                $form->get('id')->setMessages($form->get('id')->getMessages() + ['id_exist' => 'Tài khoản CRM đã được tạo trước đó!']);
                $isValid = false;
            }
            if ($isValid) {
                $data = [
                    'id' => $valuePost['id'],
                    'name' => $valuePost['name'],
                    'user_id' => $valuePost['user_id'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                $this->entityUserCrms->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                return $this->redirect()->toRoute('admin/user-crms');
            } else {
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $view->setVariable('form', $form);
        return $view;
    }

    public function editAction()
    {
        $view = new ViewModel();

        $id = $this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityUserCrms->fetchRow($id);
        if (empty($valueCurrent)) {
            $this->redirect()->toRoute('admin/user-crms');
        } else {
            $valuePost = $valueCurrent;
        }

        $form = new EditForm('edit');

        $optionUsers = $this->entityUsers->fetchAllOptions();
        $form->get('user_id')->setValueOptions(
            ['' => '--- Chọn một tài khoản fortune ---'] + $optionUsers
        );

        $request = $this->getRequest();
        if ($request->isPost()) {
            $valuePost = $request->getPost()->toArray();
            //\Zend\Debug\Debug::dump($valuePost); die();
            $form->setData($valuePost);
            if ($form->isValid()) {
                $data = [
                    'name' => $valuePost['name'],
                    'user_id' => $valuePost['user_id']
                ];
                $this->entityUserCrms->updateRow($id, $data);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
            } else {
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }else{
            $form->setData($valuePost);
        }

        $view->setVariable('form', $form);
        return $view;
    }

    public function deleteAction()
    {
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();

        $id = $this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityUserCrms->fetchRow($id);
        if (empty($valueCurrent)) {
            $this->redirect()->toRoute('admin/users');
        }

        $form = new DeleteForm($request->getRequestUri());

        if ($request->isPost()) {
            $data = ['status' => '-1'];
            $this->entityUserCrms->updateRow($id, $data);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }

        return new ViewModel([
            'form' => $form,
            'valueCurrent' => $valueCurrent
        ]);
    }

}
