<?php

/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Promotions\Controller;

use Zend\View\Model\ViewModel;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Settings\Model\Settings;
use Promotions\Model\Offers;
use Promotions\Form\Offers\SearchForm;
use Promotions\Form\Offers\AddForm;
use Promotions\Form\Offers\EditForm;
use Promotions\Form\Offers\DeleteForm;
use Storehouses\Model\Products;

class OffersController extends AdminCore
{

    public $entityOffers;
    public $entitySettings;
    public $entityProducts;

    public function __construct(
        PxtAuthentication $entityPxtAuthentication,
        Settings $entitySettings,
        Offers $entityOffers,
        Products $entityProducts
    ) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings   = $entitySettings;
        $this->entityOffers     = $entityOffers;
        $this->entityProducts   = $entityProducts;
    }

    public function indexAction()
    {
        $formSearch = new SearchForm('index');
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);

        // Lấy danh sách sản phẩm
        $optionProducts = $this->entityProducts->fetchAllOptions04();
        // Danh sách sản phẩm trúng thưởng
        $arrPromotionsProducts = array("1490" => "1490", "1712" => "1712", "1489" => "1489", "1713" => "1713", "1516" => "1516", "1517" => "1517", "1679" => "1679", "1716" => "1716", "1678" => "1678", "1707" => "1707", "1705" => "1705", "1708" => "1708", "1706" => "1706", "1717" => "1717", "1488" => "1488", "1714" => "1714");
        $optionProductFinished = [];
        foreach ($arrPromotionsProducts as $key => $item) {
            $optionProductFinished[$key] = isset($optionProducts[$key]) ? $optionProducts[$key] : "Không xác định";
        }

        $arrOffers = new Paginator(new ArrayAdapter($this->entityOffers->fetchAlls($queries)));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;

        $arrOffers->setCurrentPageNumber($page);
        $arrOffers->setItemCountPerPage($perPage);
        $arrOffers->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrOffers'         => $arrOffers,
            'contentPaginator'  => $contentPaginator,
            'formSearch'        => $formSearch,
            'queries'           => $queries,
            'optionProducts'    => $optionProductFinished
        ]);
    }

    public function addAction()
    {
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();

        $request = $this->getRequest();
        $form = new AddForm($request->getRequestUri());
        // Lấy danh sách sản phẩm
        $optionProducts = $this->entityProducts->fetchAllOptions04();
        // Danh sách sản phẩm trúng thưởng
        $arrPromotionsProducts = array("1490" => "1490", "1712" => "1712", "1489" => "1489", "1713" => "1713", "1516" => "1516", "1517" => "1517", "1679" => "1679", "1716" => "1716", "1678" => "1678", "1707" => "1707", "1705" => "1705", "1708" => "1708", "1706" => "1706", "1717" => "1717", "1488" => "1488", "1714" => "1714");
        $optionProductFinished = [];
        foreach ($arrPromotionsProducts as $key => $item) {
            $optionProductFinished[$key] = isset($optionProducts[$key]) ? $optionProducts[$key] : "Không xác định";
        }
        $form->get('product_id')->setValueOptions(['' => '--- Chọn một sản phẩm ---'] + $optionProductFinished);

        if ($request->isPost()) {
            $valuePost = $request->getPost()->toArray();

            $form->setData($valuePost);
            if ($form->isValid()) {
                $requestedAt = ($valuePost['requested_at'] != "") ? date_format(date_create_from_format('d/m/Y H:i:s', $valuePost['requested_at']), 'Y-m-d H:i:s') : \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent();
                $reponseddAt = ($valuePost['reponsed_at'] != "") ? date_format(date_create_from_format('d/m/Y H:i:s', $valuePost['reponsed_at']), 'Y-m-d H:i:s') : "";
                $content = str_replace("\n", "<br />", $valuePost['content']);
                $info = str_replace("\n", "<br />", $valuePost['info']);
                $data = [
                    'user_id'       => $this->sessionContainer->id,
                    'staff'         => $valuePost['staff'],
                    'request'       => $valuePost['request'],
                    'requested_at'  => $requestedAt,
                    'product_id'    => $valuePost['product_id'],
                    'content'       => $content,
                    'phone'         => $valuePost['phone'],
                    'info'          => $info,
                    'reponse'       => $valuePost['reponse'],
                    'reponsed_at'   => $reponseddAt,
                    'code'          => $valuePost['code'],
                    'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ];
                //\Zend\Debug\Debug::dump($data); die();
                $this->entityOffers->addRow($data);
                $this->flashMessenger()->addSuccessMessage('Thêm dữ liệu thành công!');
                die('success');
            } else {
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        }
        $view->setVariable('form', $form);
        return $view;
    }

    public function editAction()
    {
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();

        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityOffers->fetchRow($id);
        if (empty($valueCurrent)) {
            die("Không tìm thấy đề xuất này. Vui lòng kiểm tra lại!");
        } else {
            $valuePost = $valueCurrent;
        }

        $request = $this->getRequest();
        $form = new EditForm($request->getRequestUri());

        // Lấy danh sách sản phẩm
        $optionProducts = $this->entityProducts->fetchAllOptions04();
        // Danh sách sản phẩm trúng thưởng
        $arrPromotionsProducts = array("1490" => "1490", "1712" => "1712", "1489" => "1489", "1713" => "1713", "1516" => "1516", "1517" => "1517", "1679" => "1679", "1716" => "1716", "1678" => "1678", "1707" => "1707", "1705" => "1705", "1708" => "1708", "1706" => "1706", "1717" => "1717", "1488" => "1488", "1714" => "1714");
        $optionProductFinished = [];
        foreach ($arrPromotionsProducts as $key => $item) {
            $optionProductFinished[$key] = isset($optionProducts[$key]) ? $optionProducts[$key] : "Không xác định";
        }
        $form->get('product_id')->setValueOptions(['' => '--- Chọn một sản phẩm ---'] + $optionProductFinished);
        if ($request->isPost()) {
            if ((int)$valuePost["reponse"] >= 1) {
                $this->flashMessenger()->addWarningMessage('Đề xuất đã hoàn thành, không thể sửa');
                $valuePost["content"] = str_replace("<br />", "\n", $valuePost["content"]);
                $valuePost["info"] = str_replace("<br />", "\n", $valuePost["info"]);
                $valuePost['requested_at'] = date_format(date_create($valuePost['requested_at']), 'd/m/Y H:i:s');
                $valuePost['reponsed_at'] = date_format(date_create($valuePost['reponsed_at']), 'd/m/Y H:i:s');
            } else {
                $valuePost = $request->getPost()->toArray();
                $form->setData($valuePost);
                if ($form->isValid()) {
                    $requestedAt = ($valuePost['requested_at'] != "") ? date_format(date_create_from_format('d/m/Y H:i:s', $valuePost['requested_at']), 'Y-m-d H:i:s') : \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent();
                    $reponseddAt = ($valuePost['reponsed_at'] != "") ? date_format(date_create_from_format('d/m/Y H:i:s', $valuePost['reponsed_at']), 'Y-m-d H:i:s') : "";
                    $content = str_replace("\n", "<br />", $valuePost['content']);
                    $info = str_replace("\n", "<br />", $valuePost['info']);
                    $data = [
                        'user_id'       => $this->sessionContainer->id,
                        'staff'         => $valuePost['staff'],
                        'request'       => $valuePost['request'],
                        'requested_at'  => $requestedAt,
                        'product_id'    => $valuePost['product_id'],
                        'content'       => $content,
                        'phone'         => $valuePost['phone'],
                        'info'          => $info,
                        'reponse'       => $valuePost['reponse'],
                        'reponsed_at'   => $reponseddAt,
                        'code'          => $valuePost['code'],
                        'created_at' => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                    ];
                    $this->entityOffers->updateRow($id, $data);
                    $this->flashMessenger()->addSuccessMessage('Sửa dữ liệu thành công!');
                    die('success');
                } else {
                    $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
                }
            }
        } else {
            $valuePost["content"] = str_replace("<br />", "\n", $valuePost["content"]);
            $valuePost["info"] = str_replace("<br />", "\n", $valuePost["info"]);
            $valuePost['requested_at'] = date_format(date_create($valuePost['requested_at']), 'd/m/Y H:i:s');
            $valuePost['reponsed_at'] = date_format(date_create($valuePost['reponsed_at']), 'd/m/Y H:i:s');
        }

        $form->setData($valuePost);
        //die('abcdef');
        return new ViewModel([
            'form'      => $form,
            'valuePost' => $valuePost
        ]);
    }

    public function deleteAction()
    {
        $this->layout()->setTemplate('empty/layout');
        $request = $this->getRequest();

        $id = (int)$this->params()->fromRoute('id', 0);
        $valueCurrent = $this->entityOffers->fetchRow($id);
        if (empty($valueCurrent)) {
            die("Không tìm thấy đề xuất này. Vui lòng kiểm tra lại!");
        }
        $form = new DeleteForm($request->getRequestUri());

        // check relationship
        $checkRelationship = [];

        if ($request->isPost()) {
            $this->entityOffers->updateRow($id, [
                "status" => "-1"
            ]);
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
