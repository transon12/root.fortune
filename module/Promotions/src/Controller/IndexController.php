<?php

namespace Promotions\Controller;

use Promotions\Form\Index\AddPlusScoreFrom;
use Promotions\Form\Index\DeletePlusScoreForm;
use Promotions\Form\Index\EditPlusForm;
use Zend\View\Model\ViewModel;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Admin\Model\UserCrms;
use Admin\Model\Users;
use Promotions\Form\Index\StatisticListForm;
use Promotions\Form\Index\AddDialForm;
use Storehouses\Model\Products;
use Storehouses\Model\Agents;
use Supplies\Model\Supplies;
use Supplies\Model\Suppliers;
use Promotions\Model\Promotions;
use Promotions\Model\PromotionsProducts;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Settings\Model\Settings;
use Promotions\Form\Index\AddScoreForm;
use Promotions\Form\Index\AddRandomForm;
use Promotions\Form\Index\DeleteForm;
use Promotions\Form\Index\EditDialForm;
use Promotions\Form\Index\EditScoreForm;
use Promotions\Form\Index\EditRandomForm;
use Promotions\Form\Index\FinishedForm;
use Promotions\Form\Index\InputWinForm;
use Promotions\Form\Index\SearchForm;
use Promotions\Model\ListPromotions;
use Promotions\Model\WinnerPromotions;
use Promotions\Model\ListDials;
use Promotions\Model\DialsPromotions;
use Promotions\Model\WinnerDials;
use Settings\Model\Companies;
use Promotions\Form\Index\StatisticWinForm;
use Promotions\Model\LogWinners;
use Promotions\Model\PlusScore;

class IndexController extends AdminCore
{
    private $entityProducts;
    private $entityAgents;
    private $entitySupplies;
    private $entitySuppliers;
    private $entityPromotions;
    private $entityPromotionsProducts;
    private $entitySettings;
    private $entityListPromotions;
    private $entityWinnerPromotions;
    private $entityListDials;
    private $entityDialsPromotions;
    private $entityWinnerDials;
    private $entityCompanies;
    private $entityUsers;
    private $entityUserCrms;
    private $entityLogWinners;
    private $entityPlusScore;

    public function __construct(
        PxtAuthentication  $entityPxtAuthentication,
        Settings           $entitySettings,
        Products           $entityProducts,
        Agents             $entityAgents,
        Supplies           $entitySupplies,
        Suppliers          $entitySuppliers,
        Promotions         $entityPromotions,
        PromotionsProducts $entityPromotionsProducts,
        ListPromotions     $entityListPromotions,
        WinnerPromotions   $entityWinnerPromotions,
        ListDials          $entityListDials,
        DialsPromotions    $entityDialsPromotions,
        WinnerDials        $entityWinnerDials,
        Companies          $entityCompanies,
        Users              $entityUsers,
        UserCrms           $entityUserCrms,
        LogWinners         $entityLogWinners,
        PlusScore          $entityPlusScore
    )
    {
        parent::__construct($entityPxtAuthentication);
        $this->entityProducts = $entityProducts;
        $this->entityAgents = $entityAgents;
        $this->entitySupplies = $entitySupplies;
        $this->entitySuppliers = $entitySuppliers;
        $this->entityPromotions = $entityPromotions;
        $this->entityPromotionsProducts = $entityPromotionsProducts;
        $this->entitySettings = $entitySettings;
        $this->entityListPromotions = $entityListPromotions;
        $this->entityWinnerPromotions = $entityWinnerPromotions;
        $this->entityListDials = $entityListDials;
        $this->entityDialsPromotions = $entityDialsPromotions;
        $this->entityWinnerDials = $entityWinnerDials;
        $this->entityCompanies = $entityCompanies;
        $this->entityUsers = $entityUsers;
        $this->entityUserCrms = $entityUserCrms;
        $this->entityLogWinners = $entityLogWinners;
        $this->entityPlusScore = $entityPlusScore;
    }

    public function indexAction()
    {

        $formSearch = new SearchForm('index', $this->sessionContainer->id);
        if ($this->sessionContainer->id == '1') {
            $arrCompanies = $this->entityCompanies->fetchAllToOptions();
            $formSearch->get('company_id')->setValueOptions(
                [
                    '' => '--- Chọn một công ty ---',
                ] +
                $arrCompanies
            );
        }
        $queries = $this->params()->fromQuery();
        // reset company_id if empty
        if (!isset($queries['company_id']) || $queries['company_id'] == '') {
            $queries['company_id'] = isset($queries['company_id']) ? $queries['company_id'] : $this->defineCompanyId;
        }
        $formSearch->setData($queries);

        $arrPromotions = new Paginator(new ArrayAdapter($this->entityPromotions->fetchAlls($queries)));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int)$this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int)$this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;

        $arrPromotions->setCurrentPageNumber($page);
        $arrPromotions->setItemCountPerPage($perPage);
        $arrPromotions->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrPromotions' => $arrPromotions,
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'userId' => $this->sessionContainer->id,
            'optionCompanies' => $this->entityCompanies->fetchAllToOptions(),
        ]);
    }

    public function addDialAction()
    {
        if ($this->defineCompanyId == null) {
            $this->flashMessenger()->addWarningMessage('Tài khoản này không có quyền tại đây!');
            return $this->redirect()->toRoute('promotions/index');
        }
        $view = new ViewModel();
        $form = new AddDialForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if ($form->isValid()) {
                $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_begin']);
                $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_end']);
                $data = [
                    'company_id' => $this->defineCompanyId,
                    'name' => $valuePost['name'],
                    'datetime_begin' => date_format($datetimeBegin, 'Y-m-d H:i:s'),
                    'datetime_end' => date_format($datetimeEnd, 'Y-m-d H:i:s'),
                    'is_type' => 0,
                    'description' => $valuePost['description'],
                    'content' => json_encode([
                        'input_type' => $valuePost['input_type'],
                    ]),
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                    'status' => $valuePost['status'],
                ];
                $promotionId = $this->entityPromotions->addRow($data);
                if (!empty($valuePost['products_id'])) {
                    foreach ($valuePost['products_id'] as $item) {
                        $dataPromotionsProducts = [
                            'promotion_id' => $promotionId,
                            'product_id' => $item,
                            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                        ];
                        $this->entityPromotionsProducts->addRow($dataPromotionsProducts);
                    }
                }
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                return $this->redirect()->toRoute('promotions/index');
            } else {
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $arrProducts = $this->entityProducts->fetchAllOptions04(['company_id' => $this->defineCompanyId]);

        $view->setVariable('arrProducts', $arrProducts);
        $view->setVariable('form', $form);
        return $view;
    }

    public function addScoreAction()
    {
        if ($this->defineCompanyId == null) {
            $this->flashMessenger()->addWarningMessage('Tài khoản này không có quyền tại đây!');
            return $this->redirect()->toRoute('promotions/index');
        }
        $view = new ViewModel();
        $form = new AddScoreForm();

        $optionSupplies = $this->entitySupplies->fetchAllOptions(['company_id' => $this->defineCompanyId]);
//        $form->get('supplie_id')->setValueOptions(['' => '------- Chọn một vật phẩm khuyến mãi -------'] + $optionSupplies);

        $request = $this->getRequest();
        // get product
        $arrProducts = $this->entityProducts->fetchAllOptions04(['company_id' => $this->defineCompanyId]);
        $valuePost = null;
        if ($request->isPost()) {
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            // check error score_win
            // \Zend\Debug\Debug::dump($valuePost); die();
            if ($form->isValid()) {
                if ($valuePost['score_win'] != '0' && $valuePost['score_win'] != '') {
                    if ($this->checkValueProductsId($valuePost, $valuePost['score_win']) == false) {
                        $form->get('score_win')->setMessages(['score_win_min' => 'Tổng điểm trúng thưởng phải lớn hơn điểm của từng sản phẩm!']);
                    }
                }
//            dd($valuePost);
                $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_begin']);
                $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_end']);
                $data = [
                    'company_id' => COMPANY_ID,
                    'name' => $valuePost['name'],
                    'datetime_begin' => date_format($datetimeBegin, 'Y-m-d H:i:s'),
                    'datetime_end' => date_format($datetimeEnd, 'Y-m-d H:i:s'),
                    'is_type' => 1,
                    'description' => $valuePost['description'],
                    'content' => json_encode([
                        'dial' => $valuePost['dial'],
                        'limit_win' => ($valuePost['limit_win'] != '') ? $valuePost['limit_win'] : '0',
                        'score_win' => ($valuePost['score_win'] != '') ? $valuePost['score_win'] : '0',
                        'limit_message_day' => ($valuePost['limit_message_day'] != '') ? $valuePost['limit_message_day'] : '0',
                        'message_limit_day' => $valuePost['message_limit_day'],
                        'limit_message_month' => ($valuePost['limit_message_month'] != '') ? $valuePost['limit_message_month'] : '0',
                        'message_limit_month' => $valuePost['message_limit_month'],
                        'price_topup' => $valuePost['price_topup'],
                        'message_near_win' => $valuePost['message_near_win'],
                        'message_win' => $valuePost['message_win'],
                        'message_limit_win' => $valuePost['message_limit_win'],
                    ]),
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                    'status' => $valuePost['status'],
                ];
                $promotionId = $this->entityPromotions->addRow($data);

                $arrProductsId = $this->splitValueProductsId($valuePost, $arrProducts);

                if (!empty($arrProductsId)) {
                    foreach ($arrProductsId as $key => $item) {
                        $dataPromotionsProducts = [
                            'promotion_id' => $promotionId,
                            'product_id' => $key,
                            'score' => $item,
                            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                        ];
                        $this->entityPromotionsProducts->addRow($dataPromotionsProducts);
                    }
                }
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                return $this->redirect()->toRoute('promotions/index');
            } else {
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }

        }

        $view->setVariable('arrProducts', $arrProducts);
        $view->setVariable('form', $form);
        $view->setVariable('valuePost', $valuePost);
        return $view;
    }

    /**
     * Check value score_win >= score of products
     */
    public function checkValueProductsId($valuePost, $scoreWin)
    {
        if (!empty($valuePost)) {
            foreach ($valuePost as $key => $item) {
                $arrName = explode('-', $key);
                if ($arrName[0] === 'score') {
                    $id = isset($arrName[1]) ? $arrName[1] : 0;
                    if ($item > $scoreWin)
                        return false;
                }
            }
        }
        return true;
    }

    public function splitValueProductsId($valuePost, $arrProducts)
    {
        $result = [];
        if (!empty($valuePost)) {
            foreach ($valuePost as $key => $item) {
                $arrName = explode('-', $key);
                if ($arrName[0] === 'score') {
                    $id = isset($arrName[1]) ? $arrName[1] : 0;
                    if (isset($arrProducts[$id])) {
                        $result[$id] = ($item != 0 || $item != '') ? $item : 0;
                    }
                }
            }
        }
        return $result;
    }

    public function addRandomAction()
    {
        if ($this->defineCompanyId == null) {
            $this->flashMessenger()->addWarningMessage('Tài khoản này không có quyền tại đây!');
            return $this->redirect()->toRoute('promotions/index');
        }
        $view = new ViewModel();
        $form = new AddRandomForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if ($form->isValid()) {
                $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_begin']);
                $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_end']);
                $data = [
                    'company_id' => COMPANY_ID,
                    'name' => $valuePost['name'],
                    'datetime_begin' => date_format($datetimeBegin, 'Y-m-d H:i:s'),
                    'datetime_end' => date_format($datetimeEnd, 'Y-m-d H:i:s'),
                    'is_type' => 2,
                    'description' => $valuePost['description'],
                    'content' => json_encode([
                        'dial' => $valuePost['dial'],
                        'limit_win' => ($valuePost['limit_win'] != '') ? $valuePost['limit_win'] : '0',
                        'price_topup' => $valuePost['price_topup'],
                        'message_win' => $valuePost['message_win'],
                        'message_limit_win' => $valuePost['message_limit_win'],
                        'is_random' => $valuePost['is_random'],
                        'order_win' => ($valuePost['is_random'] == 0) ? $valuePost['order_win'] : '',
                    ]),
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                    'status' => $valuePost['status'],
                ];
                $promotionId = $this->entityPromotions->addRow($data);
                if (!empty($valuePost['products_id'])) {
                    foreach ($valuePost['products_id'] as $item) {
                        $dataPromotionsProducts = [
                            'promotion_id' => $promotionId,
                            'products_id' => $item,
                            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                        ];
                        $this->entityPromotionsProducts->addRow($dataPromotionsProducts);
                    }
                }
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                return $this->redirect()->toRoute('promotions/index');
            } else {
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $arrProducts = $this->entityProducts->fetchAllOptions04(['company_id' => $this->defineCompanyId]);

        $view->setVariable('arrProducts', $arrProducts);
        $view->setVariable('form', $form);
        return $view;
    }

    public function editAction()
    {
        if ($this->defineCompanyId == null) {
            $this->flashMessenger()->addWarningMessage('Tài khoản này không có quyền tại đây!');
            return $this->redirect()->toRoute('promotions/index');
        }
        $view = new ViewModel();
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityPromotions->fetchRow($id);
        if (empty($valueCurrent)) {
            $this->redirect()->toRoute('promotions/index');
        } else {
            $valuePost = $valueCurrent;
        }
        if ($valuePost['is_type'] == 2) {
            $this->editRandom($view, $id, $valuePost);
        } else if ($valuePost['is_type'] == 1) {
            $this->editScore($view, $id, $valuePost);
        } else if ($valuePost['is_type'] == 0) {
            $this->editDial($view, $id, $valuePost);
        } else {
            $this->flashMessenger()->addWarningMessage('Chương trình có lỗi, đề nghị liên hệ admin để biết thêm thông tin!');
            $this->redirect()->toRoute('promotions/index');
        }
        return $view;
    }

    public function editRandom(&$view, $id, $valuePost)
    {
        if ($this->defineCompanyId == null) {
            $this->flashMessenger()->addWarningMessage('Tài khoản này không có quyền tại đây!');
            return $this->redirect()->toRoute('promotions/index');
        }
        $form = new EditRandomForm();
        $request = $this->getRequest();

        $arrPromotionsProducts = $this->entityPromotionsProducts->fetchAllAsPromotionId($id, true);

        if ($request->isPost()) {
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if ($form->isValid()) {
                $valuePost['order_win'] = ($valuePost['is_random'] == 0) ? $valuePost['order_win'] : '';
                $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_begin']);
                $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_end']);
                $data = [
                    'name' => $valuePost['name'],
                    'datetime_begin' => date_format($datetimeBegin, 'Y-m-d H:i:s'),
                    'datetime_end' => date_format($datetimeEnd, 'Y-m-d H:i:s'),
                    'is_type' => 2,
                    'description' => $valuePost['description'],
                    'content' => json_encode([
                        'dial' => $valuePost['dial'],
                        'limit_win' => ($valuePost['limit_win'] != '') ? $valuePost['limit_win'] : '0',
                        'price_topup' => $valuePost['price_topup'],
                        'message_win' => $valuePost['message_win'],
                        'message_limit_win' => $valuePost['message_limit_win'],
                        'is_random' => $valuePost['is_random'],
                        'order_win' => $valuePost['order_win'],
                    ]),
                    'status' => $valuePost['status'],
                ];
                $this->entityPromotions->updateRow($id, $data);
                // update promotionsProducts
                $this->entityPromotionsProducts->deleteRowsAsPromotionsId($id);
                if (!empty($valuePost['products_id'])) {
                    foreach ($valuePost['products_id'] as $item) {
                        $dataPromotionsProducts = [
                            'promotion_id' => $id,
                            'product_id' => $item,
                            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                        ];
                        $this->entityPromotionsProducts->addRow($dataPromotionsProducts);
                    }
                }
                // update array promotions products
                $arrPromotionsProducts = $this->entityPromotionsProducts->fetchAllAsPromotionId($id, true);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
            } else {
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        } else {
            $valuePost['datetime_begin'] = date_format(date_create($valuePost['datetime_begin']), 'd/m/Y H:i:s');
            $valuePost['datetime_end'] = date_format(date_create($valuePost['datetime_end']), 'd/m/Y H:i:s');
            $content = json_decode($valuePost['content'], true);
            $valuePost['dial'] = $content['dial'];
            $valuePost['limit_win'] = $content['limit_win'];
            $valuePost['price_topup'] = $content['price_topup'];
            $valuePost['message_win'] = $content['message_win'];
            $valuePost['message_limit_win'] = $content['message_limit_win'];
            $valuePost['is_random'] = $content['is_random'];
            $valuePost['order_win'] = $content['order_win'];
        }
        $form->setData($valuePost);

        $arrProducts = $this->entityProducts->fetchAllOptions04(['company_id' => $this->defineCompanyId]);

        $view->setVariable('arrProducts', $arrProducts);
        $view->setVariable('arrPromotionsProducts', $arrPromotionsProducts);
        $view->setVariable('form', $form);
        $view->setTemplate('promotions/index/edit-random');
    }

    public function editScore(&$view, $id, $valuePost)
    {
        if ($this->defineCompanyId == null) {
            $this->flashMessenger()->addWarningMessage('Tài khoản này không có quyền tại đây!');
            return $this->redirect()->toRoute('promotions/index');
        }
        $form = new EditScoreForm();
        $request = $this->getRequest();
//        $optionSupplies = $this->entitySupplies->fetchAllOptions(['company_id' => $this->defineCompanyId]);
//        $form->get('supplie_id')->setValueOptions(['' => '------- Chọn một vật phẩm khuyến mãi -------'] + $optionSupplies);
        $arrPromotionsProducts = $this->entityPromotionsProducts->fetchAllAsPromotionId($id, true);

        // get product
        $arrProducts = $this->entityProducts->fetchAllOptions04(['company_id' => $this->defineCompanyId]);
        if ($request->isPost()) {
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);

            $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_begin']);
            $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_end']);
            $data = [
                'name' => $valuePost['name'],
                'datetime_begin' => date_format($datetimeBegin, 'Y-m-d H:i:s'),
                'datetime_end' => date_format($datetimeEnd, 'Y-m-d H:i:s'),
                'description' => $valuePost['description'],
                'content' => json_encode([
                    'dial' => $valuePost['dial'],
                    'limit_win' => ($valuePost['limit_win'] != '') ? $valuePost['limit_win'] : '0',
                    'score_win' => ($valuePost['score_win'] != '') ? $valuePost['score_win'] : '0',
                    'limit_message_day' => ($valuePost['limit_message_day'] != '') ? $valuePost['limit_message_day'] : '0',
                    'message_limit_day' => $valuePost['message_limit_day'],
                    'limit_message_month' => ($valuePost['limit_message_month'] != '') ? $valuePost['limit_message_month'] : '0',
                    'message_limit_month' => $valuePost['message_limit_month'],
                    'price_topup' => $valuePost['price_topup'],
                    'message_near_win' => $valuePost['message_near_win'],
                    'message_win' => $valuePost['message_win'],
                    'message_limit_win' => $valuePost['message_limit_win'],
                ]),
                'status' => $valuePost['status'],
            ];
            $this->entityPromotions->updateRow($id, $data);
            // update promotionsProducts
            $this->entityPromotionsProducts->deleteRowsAsPromotionsId($id);
            $arrProductsId = $this->splitValueProductsId($valuePost, $arrProducts);
            if (!empty($arrProductsId)) {
                foreach ($arrProductsId as $key => $item) {
                    $dataPromotionsProducts = [
                        'promotion_id' => $id,
                        'product_id' => $key,
                        'score' => $item,
                        'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                    ];
                    $this->entityPromotionsProducts->addRow($dataPromotionsProducts);
                }
            }
            // update array promotions products
            $arrPromotionsProducts = $this->entityPromotionsProducts->fetchAllAsPromotionId($id, true);
            $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');

        } else {
            $valuePost['datetime_begin'] = date_format(date_create($valuePost['datetime_begin']), 'd/m/Y H:i:s');
            $valuePost['datetime_end'] = date_format(date_create($valuePost['datetime_end']), 'd/m/Y H:i:s');
            $content = json_decode($valuePost['content'], true);
            $valuePost['dial'] = $content['dial'];
            $valuePost['limit_win'] = $content['limit_win'];
            $valuePost['limit_message_day'] = $content['limit_message_day'];
            $valuePost['limit_message_month'] = $content['limit_message_month'];
            $valuePost['message_limit_day'] = $content['message_limit_day'];
            $valuePost['message_limit_month'] = $content['message_limit_month'];
            $valuePost['score_win'] = $content['score_win'];
            $valuePost['price_topup'] = $content['price_topup'];
            $valuePost['message_near_win'] = $content['message_near_win'];
            $valuePost['message_win'] = $content['message_win'];
            $valuePost['message_limit_win'] = $content['message_limit_win'];
        }
        $form->setData($valuePost);

        $view->setVariable('arrProducts', $arrProducts);
        $view->setVariable('arrPromotionsProducts', $arrPromotionsProducts);
        $view->setVariable('form', $form);
        $view->setTemplate('promotions/index/edit-score');
    }

    public function editDial(&$view, $id, $valuePost)
    {
        if ($this->defineCompanyId == null) {
            $this->flashMessenger()->addWarningMessage('Tài khoản này không có quyền tại đây!');
            return $this->redirect()->toRoute('promotions/index');
        }
        $form = new EditDialForm();
        $request = $this->getRequest();

        $arrPromotionsProducts = $this->entityPromotionsProducts->fetchAllAsPromotionId($id, true);

        if ($request->isPost()) {
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if ($form->isValid()) {
                $datetimeBegin = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_begin']);
                $datetimeEnd = date_create_from_format('d/m/Y H:i:s', $valuePost['datetime_end']);
                $data = [
                    'name' => $valuePost['name'],
                    'datetime_begin' => date_format($datetimeBegin, 'Y-m-d H:i:s'),
                    'datetime_end' => date_format($datetimeEnd, 'Y-m-d H:i:s'),
                    'description' => $valuePost['description'],
                    'content' => json_encode([
                        'input_type' => $valuePost['input_type'],
                    ]),
                    'status' => $valuePost['status'],
                ];
                $this->entityPromotions->updateRow($id, $data);
                // update promotionsProducts
                $this->entityPromotionsProducts->deleteRowsAsPromotionsId($id);
                if (!empty($valuePost['products_id'])) {
                    foreach ($valuePost['products_id'] as $item) {
                        $dataPromotionsProducts = [
                            'promotion_id' => $id,
                            'product_id' => $item,
                            'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent(),
                        ];
                        $this->entityPromotionsProducts->addRow($dataPromotionsProducts);
                    }
                }
                // update array promotions products
                $arrPromotionsProducts = $this->entityPromotionsProducts->fetchAllAsPromotionId($id, true);
                $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
            } else {
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        } else {
            $valuePost['datetime_begin'] = date_format(date_create($valuePost['datetime_begin']), 'd/m/Y H:i:s');
            $valuePost['datetime_end'] = date_format(date_create($valuePost['datetime_end']), 'd/m/Y H:i:s');
            $content = json_decode($valuePost['content'], true);
            $valuePost['input_type'] = $content['input_type'];
        }
        $form->setData($valuePost);
        $arrProducts = $this->entityProducts->fetchAllOptions04(['company_id' => $this->defineCompanyId]);

        $view->setVariable('arrProducts', $arrProducts);
        $view->setVariable('arrPromotionsProducts', $arrPromotionsProducts);
        $view->setVariable('form', $form);
        $view->setTemplate('promotions/index/edit-dial');
    }

    public function deleteAction()
    {
        if ($this->defineCompanyId == null) {
            $this->flashMessenger()->addWarningMessage('Tài khoản này không có quyền tại đây!');
            return $this->redirect()->toRoute('promotions/index');
        }
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();

        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityPromotions->fetchRow($id);
        if (empty($valueCurrent)) {
            $this->redirect()->toRoute('promotions/index');
        }
        $form = new DeleteForm($request->getRequestUri());

        // check relationship
        $checkRelationship = [];
        //         $countListPromotions = $this->entityListPromotions->fetchCount(['promotion_id' => $id]);
        //         $countWinnerPromotions = $this->entityWinnerPromotions->fetchCount(['promotion_id' => $id]);
        //         $countPromotionsProducts = $this->entityPromotionsProducts->fetchCount(['promotion_id' => $id]);
        //         $countListDials = $this->entityListDials->fetchCount(['promotion_id' => $id]);
        //         $countDialsPromotions = $this->entityDialsPromotions->fetchCount(['promotion_id' => $id]);
        //\Zend\Debug\Debug::dump($countListPromotions);
        // echo $countListPromotions . " - " . $countWinnerPromotions . " - " . $countPromotionsProducts . " - " . $countListDials . " - " . $countDialsPromotions;
        //die();
        //         if($countListPromotions > 0){
        //             $checkRelationship['list_promotions'] = 1;
        //         }
        //         if($countWinnerPromotions > 0){
        //             $checkRelationship['winner_promotions'] = 1;
        //         }
        //         if($countPromotionsProducts > 0){
        //             $checkRelationship['promotions_products'] = 1;
        //         }
        //         if($countListDials > 0){
        //             $checkRelationship['list_dials'] = 1;
        //         }
        //         if($countDialsPromotions > 0){
        //             $checkRelationship['dials_promotions'] = 1;
        //         }

        if ($request->isPost()) {
            // delete all list promotions
            //             $this->entityListPromotions->deleteRows(['promotion_id' => $id]);
            //             // delete all winner promotions
            //             $this->entityWinnerPromotions->deleteRows(['promotion_id' => $id]);
            //             // delete all promotions_products
            //             $this->entityPromotionsProducts->deleteRows(['promotion_id' => $id]);
            //             // delete all winner dials as prizes
            //             $arrListDials = $this->entityListDials->fetchAllConditions(['promotion_id' => $id]);
            //             if(!empty($arrListDials)){
            //                 foreach($arrListDials as $item){
            //                     $this->entityWinnerDials->deleteRows(['list_dial_id' => $item['id']]);
            //                 }
            //             }
            //             // delete all list dials
            //             $this->entityListDials->deleteRows(['promotion_id' => $id]);
            //             // delete all dials promotions
            //             $this->entityDialsPromotions->deleteRows(['promotion_id' => $id]);
            // delete promotion
            $this->entityPromotions->updateRow($id, ['status' => '-1']);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            die('success');
        }

        return new ViewModel([
            'form' => $form,
            'checkRelationship' => $checkRelationship,
            'valueCurrent' => $valueCurrent,
        ]);
    }

    public function logsAction()
    {
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();

        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityWinnerPromotions->fetchRow($id);
        if (empty($valueCurrent)) {
            $this->redirect()->toRoute('promotions/index');
        }

        $optionPromotions = $this->entityPromotions->fetchAllOptions01(['company_id' => COMPANY_ID, 'dial' => 0]);

        $arrLogWinners = $this->entityLogWinners->fetchAlls(['winner_promotion_id' => $id]);

        $optionUsers = $this->entityUsers->fetchAllOptions();

        $optionProducts = $this->entityProducts->fetchAllOptions04();
        $optionAgents = $this->entityAgents->fetchAllOptions();

        return new ViewModel([
            'valueCurrent' => $valueCurrent,
            'optionPromotions' => $optionPromotions,
            'arrLogWinners' => $arrLogWinners,
            'optionUsers' => $optionUsers,
            'optionProducts' => $optionProducts,
            'optionAgents' => $optionAgents,
        ]);
    }

    public function iframeAction()
    {
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityPromotions->fetchRow($id);
        if (empty($valueCurrent)) {
            die('success');
        }
        $view->setVariable('id', $id);
        return $view;
    }

    public function statisticListAction()
    {
        $formSearch = new StatisticListForm();
        $optionPromotions = $this->entityPromotions->fetchAllOptions01(['company_id' => COMPANY_ID, 'dial' => 0]);
        // \Zend\Debug\Debug::dump($optionPromotions); die();
        $formSearch->get('promotion_id')->setValueOptions(['' => '------- Chọn một chương trình khuyến mãi -------'] + $optionPromotions);

        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        if (isset($queries['btnExport'])) {
            if ($this->sessionContainer->id == "1" || $this->sessionContainer->id == "20071") {
                $this->statisticListExcel($queries);
            } else {
                $this->flashMessenger()->addWarningMessage('Tài khoản không có quyền xuất dữ liệu!');
            }
        }

        $optionListPromotion = $this->entityPromotionsProducts->fetchAllOptionKeyPromotion();
        $optionProduct = $this->entityProducts->fetchAllOptions01();
        $optionAgent = $this->entityAgents->fetchAllOptions01();
        //	$optionMessage = $this->entityMessages -> fetchCountAll();
        $arrListPromotions = new Paginator(new ArrayAdapter($this->entityListPromotions->fetchAlls($queries + ['company_id' => COMPANY_ID])));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int)$this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int)$this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;

        $arrListPromotions->setCurrentPageNumber($page);
        $arrListPromotions->setItemCountPerPage($perPage);
        $arrListPromotions->setPageRange($contentPaginator['page_range']);


        // if (in_array($_SERVER['REMOTE_ADDR'], array("1.53.114.228"))) {
        //     \Zend\Debug\Debug::dump($optionProduct);
        //     die("<br>-1| Ip " . $_SERVER['REMOTE_ADDR'] . " is deny! Contact Administrator, please!");
        // }

        return new ViewModel([
            'arrListPromotions' => $arrListPromotions,
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'optionListPromotion' => $optionListPromotion,
            'optionProduct' => $optionProduct,
            'optionAgent' => $optionAgent
            //		'optionMessage' => $optionMessage
        ]);
    }

    public function statisticListExcel($queries = null)
    {
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        // get data
        $arrListPromotions = $this->entityListPromotions->fetchAlls($queries + ['company_id' => COMPANY_ID]);
        if (empty($arrListPromotions)) return true;
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        } else {
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xuất dữ liệu, đề nghị liên hệ Admin!');
            return $this->redirect()->toRoute('statistics/promotions');
        }
        // Đặt tên file
        $path = "Thong-ke-tin-nhan-" . date('d-m-Y-H-i-s') . ".xlsx";

        /* Tạo mới một đối tượng PHPExcel */
        $objPHPExcel = new \PHPExcel();
        /* Cài đặt Properties */
        $objPHPExcel->getProperties()->setCreator("Pxt Creator")
            ->setLastModifiedBy("Pxt Modified")
            ->setTitle("Pxt Title")
            ->setSubject("Pxt Subject")
            ->setDescription("Pxt Description")
            ->setKeywords("Pxt Keywords")
            ->setCategory("Pxt Category");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')
            ->setSize(12); /* Cài đặt font cho cả file */

        /* Cài đặt chiều rộng cho từng ô */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

        /* Chỉnh dòng đầu tiên */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1'); /* Nhóm cột */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Thống kê danh sách tham gia khuyến mãi");
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor(new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_DARKGREEN));

        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', 'STT')
            ->setCellValue('B2', 'Mã PIN')
            ->setCellValue('C2', 'Số điện thoại')
            ->setCellValue('D2', 'Sản phẩm')
            ->setCellValue('E2', 'Điểm')
            ->setCellValue('F2', 'Ngày tham gia');
        $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getFont()->setBold(true);
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $optionProduct = $this->entityProducts->fetchAllOptions01();
        // Truyền dữ liệu vào file
        $i = 3; // số bắt đầu
        $j = 1;
        $arrIsType = Promotions::returnIsType();
        foreach ($arrListPromotions as $item) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue("A" . $i, $j)
                ->setCellValue("B" . $i, $item['code_id'])
                ->setCellValue("C" . $i, $item['phone_id'])
                ->setCellValue("D" . $i, (isset($optionProduct[$item["product_id"]]) ? $optionProduct[$item["product_id"]] : "Không xác định"))
                ->setCellValue("E" . $i, $item['score'])
                ->setCellValue("F" . $i, "'" . date_format(date_create($item['created_at']), 'd/m/Y H:i:s'));
            $i++;
            $j++;
        }
        //Zend_Debug::dump($arrMessage); die("PXT");
        unset($arrListPromotions);
        // Redirect output to a client’s web browser (Excel2007)
        ob_end_clean(); /* Xoá (hay loại bỏ) bất kỳ khoảng trắng trong cặp lệnh này*/
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $path . '"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');

        ob_end_clean();
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        return true;
    }

    public function statisticWinAction()
    {
        $formSearch = new StatisticWinForm();
        $optionPromotions = $this->entityPromotions->fetchAllOptions01(['company_id' => COMPANY_ID, 'dial' => 0]);
        $formSearch->get('promotion_id')->setValueOptions(['' => '------- Chọn một chương trình khuyến mãi -------'] + $optionPromotions);

        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        if (isset($queries['btnExport'])) {
            if ($this->sessionContainer->id == "268") {
                $this->statisticWinExcel($queries);
            } else {
                $this->flashMessenger()->addWarningMessage('Tài khoản không có quyền xuất dữ liệu!');
            }
        }
        // Chỉ lấy khi chưa được xử lý
        $queries["status_order"] = 0;

        $arrWinnerPromotions = new Paginator(new ArrayAdapter($this->entityWinnerPromotions->fetchAlls($queries)));
        // get setting paginator
        $settingPaginator = $this->entitySettings->fetchRow('paginators');
        // \Zend\Debug\Debug::dump($settingPaginator);
        // die();
        $contentPaginator = json_decode($settingPaginator['content'], true);
        // set page
        $page = (int)$this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int)$this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;

        $arrWinnerPromotions->setCurrentPageNumber($page);
        $arrWinnerPromotions->setItemCountPerPage($perPage);
        $arrWinnerPromotions->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrWinnerPromotions' => $arrWinnerPromotions,
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'optionPromotions' => $optionPromotions,
        ]);
    }

    public function iframeStatisticWinAction()
    {
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityWinnerPromotions->fetchRow($id);
        if (empty($valueCurrent)) {
            die('success');
        }
        $view->setVariable('id', $id);
        return $view;
    }

    public function inputWinAction()
    {
        // \Zend\Debug\Debug::dump($this->sessionContainer->permissions);
        // \Zend\Debug\Debug::dump($this->sessionContainer->not_permissions);
        // die();
        $formSearch = new InputWinForm();
        $optionPromotions = $this->entityPromotions->fetchAllOptions01(['company_id' => COMPANY_ID, 'dial' => 0]);
        $formSearch->get('promotion_id')->setValueOptions(['' => '------- Chọn một chương trình khuyến mãi -------'] + $optionPromotions);

        $arrUserCrms = $this->entityUserCrms->fetchAllOptions();
        $optionUserCrms = [];
        foreach ($arrUserCrms as $k => $v) {
            $optionUserCrms[$k] = $v["id"] . " (" . $v["id"] . ")";
        }
        $formSearch->get('user_crm_id')->setValueOptions(['' => '------- Chọn một tài khoản CRM -------'] + $optionUserCrms);

        $optionUsers = $this->entityUsers->fetchAllOptions();
        $formSearch->get('user_id')->setValueOptions(['' => '------- Chọn một tài khoản fortune -------'] + $optionUsers);

        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        // Chỉ lấy khi chưa được xử lý
        $queries["status_order"] = 2;
        if ($this->sessionContainer->id != "268") {
            $queries["user_input"] = $this->sessionContainer->id;
        }

        $arrWinnerPromotions = new Paginator(new ArrayAdapter($this->entityWinnerPromotions->fetchAlls($queries)));
        // get setting paginator
        $settingPaginator = $this->entitySettings->fetchRow('paginators');
        // \Zend\Debug\Debug::dump($settingPaginator);
        // die();
        $contentPaginator = json_decode($settingPaginator['content'], true);
        // set page
        $page = (int)$this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int)$this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;

        $arrWinnerPromotions->setCurrentPageNumber($page);
        $arrWinnerPromotions->setItemCountPerPage($perPage);
        $arrWinnerPromotions->setPageRange($contentPaginator['page_range']);

        // Lấy danh sách tài khoản
        $optionUsers = $this->entityUsers->fetchAllOptions();
        return new ViewModel([
            'arrWinnerPromotions' => $arrWinnerPromotions,
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'optionPromotions' => $optionPromotions,
            'optionUsers' => $optionUsers,
            'userId' => $this->sessionContainer->id,
        ]);
    }

    public function iframeInputWinAction()
    {
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityWinnerPromotions->fetchRow($id);
        if (empty($valueCurrent)) {
            die('success');
        }
        // Kiểm tra có đúng tài khoản được chỉ định đang thao tác hay không
        if ($valueCurrent["user_input"] != $this->sessionContainer->id) {
            if ($this->sessionContainer->id != "268") {
                $this->flashMessenger()->addWarningMessage('Tài khoản này không có quyền cập nhật cho sđt ' . $valueCurrent["phone_id"]);
                die('success');
            }
        }
        $view->setVariable('id', $id);
        return $view;
    }

    public function rewardAction()
    {
        $formSearch = new InputWinForm();
        $optionPromotions = $this->entityPromotions->fetchAllOptions01(['company_id' => COMPANY_ID, 'dial' => 0]);
        $formSearch->get('promotion_id')->setValueOptions(['' => '------- Chọn một chương trình khuyến mãi -------'] + $optionPromotions);

        $arrUserCrms = $this->entityUserCrms->fetchAllOptions();
        $optionUserCrms = [];
        foreach ($arrUserCrms as $k => $v) {
            $optionUserCrms[$k] = $v["id"] . " (" . $v["id"] . ")";
        }
        $formSearch->get('user_crm_id')->setValueOptions(['' => '------- Chọn một tài khoản CRM -------'] + $optionUserCrms);

        $optionUsers = $this->entityUsers->fetchAllOptions();
        $formSearch->get('user_id')->setValueOptions(['' => '------- Chọn một tài khoản fortune -------'] + $optionUsers);

        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        // Chỉ lấy khi chưa được xử lý
        $queries["status_order"] = 3;

        $arrWinnerPromotions = new Paginator(new ArrayAdapter($this->entityWinnerPromotions->fetchAlls($queries)));
        // get setting paginator
        $settingPaginator = $this->entitySettings->fetchRow('paginators');
        // \Zend\Debug\Debug::dump($settingPaginator);
        // die();
        $contentPaginator = json_decode($settingPaginator['content'], true);
        // set page
        $page = (int)$this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int)$this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;

        $arrWinnerPromotions->setCurrentPageNumber($page);
        $arrWinnerPromotions->setItemCountPerPage($perPage);
        $arrWinnerPromotions->setPageRange($contentPaginator['page_range']);

        // Lấy danh sách tài khoản
        $optionUsers = $this->entityUsers->fetchAllOptions();
        return new ViewModel([
            'arrWinnerPromotions' => $arrWinnerPromotions,
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'optionPromotions' => $optionPromotions,
            'optionUsers' => $optionUsers,
            'userId' => $this->sessionContainer->id,
        ]);
    }

    public function iframeRewardAction()
    {
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityWinnerPromotions->fetchRow($id);
        if (empty($valueCurrent)) {
            die('success');
        }
        $view->setVariable('id', $id);
        return $view;
    }

    public function finishedAction()
    {
        $formSearch = new FinishedForm();
        $optionPromotions = $this->entityPromotions->fetchAllOptions01(['company_id' => COMPANY_ID, 'dial' => 0]);
        $formSearch->get('promotion_id')->setValueOptions(['' => '------- Chọn một chương trình khuyến mãi -------'] + $optionPromotions);

        $arrUserCrms = $this->entityUserCrms->fetchAllOptions();
        $optionUserCrms = [];
        foreach ($arrUserCrms as $k => $v) {
            $optionUserCrms[$k] = $v["id"] . " (" . $v["id"] . ")";
        }
        $formSearch->get('user_crm_id')->setValueOptions(['' => '------- Chọn một tài khoản CRM -------'] + $optionUserCrms);

        $optionUsers = $this->entityUsers->fetchAllOptions();
        $formSearch->get('user_id')->setValueOptions(['' => '------- Chọn một tài khoản fortune -------'] + $optionUsers);

        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);

        if (isset($queries['btnExport'])) {
            if ($this->sessionContainer->id == "268" || $this->sessionContainer->id == "271") {
                $this->finishedExcel($queries);
            } else {
                $this->flashMessenger()->addWarningMessage('Tài khoản không có quyền xuất dữ liệu!');
            }
        }
        // Chỉ lấy khi đã được xử lý
        $queries["status_order"] = 1;
        $queries["is_join"] = 1;

        $arrWinnerPromotions = new Paginator(new ArrayAdapter($this->entityWinnerPromotions->fetchAlls($queries)));
        // get setting paginator
        $settingPaginator = $this->entitySettings->fetchRow('paginators');
        // \Zend\Debug\Debug::dump($settingPaginator);
        // die();
        $contentPaginator = json_decode($settingPaginator['content'], true);
        // set page
        $page = (int)$this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int)$this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;

        $arrWinnerPromotions->setCurrentPageNumber($page);
        $arrWinnerPromotions->setItemCountPerPage($perPage);
        $arrWinnerPromotions->setPageRange($contentPaginator['page_range']);

        // Lấy danh sách tài khoản
        $optionUsers = $this->entityUsers->fetchAllOptions();
        return new ViewModel([
            'arrWinnerPromotions' => $arrWinnerPromotions,
            'contentPaginator' => $contentPaginator,
            'formSearch' => $formSearch,
            'queries' => $queries,
            'optionPromotions' => $optionPromotions,
            'optionUsers' => $optionUsers,
            'userId' => $this->sessionContainer->id,
        ]);
    }

    public function iframeFinishedAction()
    {
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityWinnerPromotions->fetchRow($id);
        if (empty($valueCurrent)) {
            die('success');
        }
        $view->setVariable('id', $id);
        return $view;
    }

    public function statisticWinExcel($queries = null)
    {
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        // get data
        $arrWinnerPromotions = $this->entityWinnerPromotions->fetchAlls($queries);
        if (empty($arrWinnerPromotions)) return true;
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        } else {
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xuất dữ liệu, đề nghị liên hệ Admin!');
            return $this->redirect()->toRoute('promotions/index', ['action' => 'statistic-win']);
        }
        // Đặt tên file
        $path = "Thong-ke-trung-thuong-khuyen-mai-" . date('d-m-Y-H-i-s') . ".xlsx";

        /* Tạo mới một đối tượng PHPExcel */
        $objPHPExcel = new \PHPExcel();
        /* Cài đặt Properties */
        $objPHPExcel->getProperties()->setCreator("Pxt Creator")
            ->setLastModifiedBy("Pxt Modified")
            ->setTitle("Pxt Title")
            ->setSubject("Pxt Subject")
            ->setDescription("Pxt Description")
            ->setKeywords("Pxt Keywords")
            ->setCategory("Pxt Category");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')
            ->setSize(12); /* Cài đặt font cho cả file */

        /* Cài đặt chiều rộng cho từng ô */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(100);

        $objPHPExcel->getActiveSheet()->getStyle('A1:F999')
            ->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F999')
            ->getAlignment()->setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        /* Chỉnh dòng đầu tiên */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1'); /* Nhóm cột */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Thống kê danh sách trúng thưởng khuyến mãi");
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor(new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_DARKGREEN));

        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', 'STT')
            ->setCellValue('B2', 'Số điện thoại')
            ->setCellValue('C2', 'Chương trình khuyến mãi')
            ->setCellValue('D2', 'Điểm')
            ->setCellValue('E2', 'Ngày trúng thưởng')
            ->setCellValue('F2', 'Ghi chú');
        $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getFont()->setBold(true);
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        // Truyền dữ liệu vào file
        $i = 3; // số bắt đầu
        $j = 1;
        $optionPromotions = $this->entityPromotions->fetchAllOptions01(['company_id' => COMPANY_ID, 'dial' => 0]);
        foreach ($arrWinnerPromotions as $item) {
            $note1 = str_replace("<br />", "\n", $item['note_1']);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue("A" . $i, $j)
                ->setCellValue("B" . $i, $item['phone_id'])
                ->setCellValue("C" . $i, (isset($optionPromotions[$item['promotion_id']]) ? $optionPromotions[$item['promotion_id']] : ""))
                ->setCellValue("D" . $i, $item['score'])
                ->setCellValue("E" . $i, date_format(date_create($item['created_at']), 'd/m/Y H:i:s'))
                ->setCellValue("F" . $i, $note1);
            $i++;
            $j++;
        }
        //Zend_Debug::dump($arrMessage); die("PXT");
        unset($arrWinnerPromotions);
        // Redirect output to a client’s web browser (Excel2007)
        ob_end_clean(); /* Xoá (hay loại bỏ) bất kỳ khoảng trắng trong cặp lệnh này*/
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $path . '"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');

        ob_end_clean();
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        return true;
    }

    public function finishedExcel($queries = null)
    {
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        // Chỉ lấy khi đã được xử lý
        $queries["status_order"] = 1;
        // get data
        $arrWinnerPromotions = $this->entityWinnerPromotions->fetchAlls($queries);
        if (empty($arrWinnerPromotions)) return true;
        // path file
        $file = APPLICATION_PATH . '/vendor/PHPExcel.php';
        if (is_file($file)) {
            require_once($file);
        } else {
            $this->flashMessenger()->addWarningMessage('Có lỗi trong quá trình xuất dữ liệu, đề nghị liên hệ Admin!');
            return $this->redirect()->toRoute('promotions/index', ['action' => 'statistic-win']);
        }
        // Đặt tên file
        $path = "Thong-ke-tra-thuong-" . date('d-m-Y-H-i-s') . ".xlsx";

        /* Tạo mới một đối tượng PHPExcel */
        $objPHPExcel = new \PHPExcel();
        /* Cài đặt Properties */
        $objPHPExcel->getProperties()->setCreator("Pxt Creator")
            ->setLastModifiedBy("Pxt Modified")
            ->setTitle("Pxt Title")
            ->setSubject("Pxt Subject")
            ->setDescription("Pxt Description")
            ->setKeywords("Pxt Keywords")
            ->setCategory("Pxt Category");
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')
            ->setSize(12); /* Cài đặt font cho cả file */

        /* Cài đặt chiều rộng cho từng ô */
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(45);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(80);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(60);

        $objPHPExcel->getActiveSheet()->getStyle('A1:L999')
            ->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:L999')
            ->getAlignment()->setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        /* Chỉnh dòng đầu tiên */
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:L1'); /* Nhóm cột */
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("Thống kê danh sách trúng thưởng khuyến mãi");
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setSize(16)
            ->setBold(true)
            ->setColor(new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_DARKGREEN));

        // set atribute row 2
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', 'STT')
            ->setCellValue('B2', 'Số điện thoại')
            ->setCellValue('C2', 'Chương trình khuyến mãi')
            ->setCellValue('D2', 'Điểm')
            ->setCellValue('E2', 'Ngày trúng thưởng')
            ->setCellValue('F2', 'Ngày trả thưởng')
            ->setCellValue('G2', 'Tài khoản cập nhật')
            ->setCellValue('H2', 'Người phụ trách')
            ->setCellValue('I2', 'Mã đơn trả thưởng')
            ->setCellValue('J2', 'Tên người nhận thưởng')
            ->setCellValue('K2', 'Địa chỉ nhận thưởng')
            ->setCellValue('L2', 'Ghi chú');
        $objPHPExcel->getActiveSheet()->getStyle('A2:L2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:L2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:L2')->getFont()->setBold(true);
        // set attribute col center
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('I')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('J')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        // Truyền dữ liệu vào file
        $i = 3; // số bắt đầu
        $j = 1;
        // Lấy danh sách tài khoản
        $optionUsers = $this->entityUsers->fetchAllOptions();

        $optionPromotions = $this->entityPromotions->fetchAllOptions01(['company_id' => COMPANY_ID, 'dial' => 0]);
        foreach ($arrWinnerPromotions as $item) {
            $note3 = str_replace("<br />", "\n", $item['note_3']);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue("A" . $i, $j)
                ->setCellValue("B" . $i, $item['phone_id'])
                ->setCellValue("C" . $i, (isset($optionPromotions[$item['promotion_id']]) ? $optionPromotions[$item['promotion_id']] : ""))
                ->setCellValue("D" . $i, $item['score'])
                ->setCellValue("E" . $i, date_format(date_create($item['created_at']), 'd/m/Y H:i:s'))
                ->setCellValue("F" . $i, date_format(date_create($item['finished_at']), 'd/m/Y H:i:s'))
                ->setCellValue("G" . $i, (isset($optionUsers[$item["user_input"]]) ? $optionUsers[$item["user_input"]] : "Không xác định"))
                ->setCellValue("H" . $i, ($item["user_input_id"] != "" && $item["user_input_name"] != "") ? ($item["user_input_id"] . " (" . $item["user_input_name"] . ")") : "Không xác định")
                ->setCellValue("I" . $i, $item['code_order'])
                ->setCellValue("J" . $i, $item['fullname_recipient'])
                ->setCellValue("K" . $i, $item['address_recipient'])
                ->setCellValue("L" . $i, $note3);
            $i++;
            $j++;
        }
        //Zend_Debug::dump($arrMessage); die("PXT");
        unset($arrWinnerPromotions);
        // Redirect output to a client’s web browser (Excel2007)
        ob_end_clean(); /* Xoá (hay loại bỏ) bất kỳ khoảng trắng trong cặp lệnh này*/
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $path . '"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');

        ob_end_clean();
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        return true;
    }

    public function addAction()
    {
        if ($this->defineCompanyId == null) {
            $this->flashMessenger()->addWarningMessage('Tài khoản này không có quyền tại đây!');
            return $this->redirect()->toRoute('promotions/index');
        }
        $view = new ViewModel();
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityPromotions->fetchRow($id);
        $valuePost = $valueCurrent;
        return $view;
    }

    public function plusScoreAction()
    {
        $arrPlusScore = $this->entityPlusScore->fetchAlls();
        return new ViewModel([
            'arrayPlusScore' => $arrPlusScore,
        ]);
    }

    public function deletePlusScoreAction()
    {
        $form = new DeletePlusScoreForm($request->getRequestUri());
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();

        $id = (int)$this->params()->fromRoute('id', 0);

        if ($request->isPost()) {
            $this->entityPlusScore->deleteRow($id);
            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
        }
        return new ViewModel(['from' => $form]);
    }

    public function addPlusScoreAction()
    {
        $request = $this->getRequest();
        $form = new AddPlusScoreFrom();
        $id = (int)$this->params()->fromRoute('id', 0);
        $valuePost = $request->getPost()->toArray();
        $form->setData($valuePost);
        if ($form->isValid()) {
            $data = [
                'score' => $valuePost['score'],
                'message_win' => $valuePost['message_win'],
            ];
            $this->entityPlusScore->addRow($data);

            $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
            return $this->redirect()->toRoute('promotions/plusScore');

        } else {
            $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
        }

        return new ViewModel(['form' => $form]);
    }

    public function EditPlusScoreAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityPlusScore->firstRow($id);
        $init = [];
        $form = new EditPlusForm();

        if (empty($valueCurrent)) {
            $this->redirect()->toRoute('promotions/dials');
        } else {
            $init['score'] = $valueCurrent['score'];
            $init['message_win'] = $valueCurrent['message_win'];
        }

        $form->setData($init);
        $request = $this->getRequest();
        $valuePost = $request->getPost()->toArray();
        $form->setData($valuePost);
        if ($request->isPost()) {
            if ($form->isValid()) {
                $data = [
                    'score' => $valuePost['score'],
                    'message_win' => $valuePost['message_win'],
                ];
                $this->entityPlusScore->updateRow($id, $data);

                $this->flashMessenger()->addSuccessMessage('Xóa dữ liệu thành công!');
                return $this->redirect()->toRoute('promotions/plusScore');

            } else {
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }

        return new ViewModel(['form' => $form]);
    }
}
