<?php

namespace Promotions\Controller;

use Zend\View\Model\ViewModel;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Admin\Model\UserCrms;
use Admin\Model\Users;
use Promotions\Form\Order\FinishedForm;
use Promotions\Form\Order\IndexForm;
use Promotions\Form\Order\InputForm;
use Promotions\Form\Order\RewardForm;
use Storehouses\Model\Products;
use Promotions\Model\Promotions;
use Promotions\Model\PromotionsProducts;
use Settings\Model\Settings;
use Promotions\Model\ListPromotions;
use Promotions\Model\WinnerPromotions;
use Promotions\Model\ListDials;
use Promotions\Model\DialsPromotions;
use Promotions\Model\LogWinners;
use Promotions\Model\WinnerDials;
use Settings\Model\Companies;
use SoapHeader;
use Promotions\Model\PlusScore;

class OrderController extends AdminCore
{
    private $entityProducts;
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
    // Danh sách tài khoản
    private $userCrms = [];

    public function setUserCrms()
    {
        $arrUserCrms = $this->entityUserCrms->fetchAllOptions();
        foreach ($arrUserCrms as $k => $v) {
            $this->userCrms[$k] = [
                "id" => $v["user_id"],
                "name" => $v["name"]
            ];
        }
        /*
        // admin
        $this->userCrms["Hoanh.NTH"] = ["id" => 268, "name" => "Hoanh NTH"];
        $this->userCrms["datachung.snk"] = ["id" => 268, "name" => "Data Chung SNK"];
        // htp.social
        $this->userCrms["Anh.VTQ"] = ["id" => 272, "name" => "Vũ Thị Quỳnh Anh"];
        $this->userCrms["Truc.NTHO"] = ["id" => 272, "name" => "Nguyễn Thị Hoàng Oanh Trúc"];
        $this->userCrms["Sang.VTH"] = ["id" => 272, "name" => "Võ Thị Hồng Sang"];
        $this->userCrms["Marketing1"] = ["id" => 272, "name" => "Nguyễn Thị Kim Thoa"];
        $this->userCrms["Marketing2"] = ["id" => 272, "name" => "MKT Mi Sen"];
        $this->userCrms["CSKH.MKT1"] = ["id" => 272, "name" => "Ngọc Tây - CSKH MKT1"];
        $this->userCrms["Sale.MKT2"] = ["id" => 272, "name" => "QuáchTiên - Sale MKT2"];
        $this->userCrms["Sale.MKT1"] = ["id" => 272, "name" => "Nguyễn Ngọc - Sale MKT1"];
        $this->userCrms["Sale.MKT2-01"] = ["id" => 272, "name" => "Linh Chi - Sale MKT2"];
        $this->userCrms["Sale.MKT1-01"] = ["id" => 272, "name" => "Đông Hồ - Sale MKT1"];
        // htp.otc
        $this->userCrms["giamy.otc"] = ["id" => 271, "name"  => "TLS Huỳnh Gia Mỹ"];
        $this->userCrms["nhuhao.otc"] = ["id" => 271, "name"  => "TLS Nguyễn Thị Như Hảo"];
        $this->userCrms["nhungoc.otc"] = ["id" => 271, "name"  => "TLS Trương Thị Như Ngọc"];
        $this->userCrms["mylinh.otc"] = ["id" => 271, "name"  => "TLS Lê Thị Mỹ Linh"];
        $this->userCrms["kieutrinh.otc"] = ["id" => 271, "name"  => "TLS Quách Thị Kiều Trinh"];
        $this->userCrms["anhthu.otc"] = ["id" => 271, "name"  => "TLS Trương Thị Anh Thư"];
        $this->userCrms["kimngoc.otc"] = ["id" => 271, "name"  => "Kim Ngọc"];
        $this->userCrms["otcmiennam2"] = ["id" => 271, "name"  => "Admin OTC Bích Ngọc"];
        $this->userCrms["thaotrinh.otc"] = ["id" => 271, "name"  => "Admin TLS Nguyễn Thảo Trinh"];
        // htp.cskh
        $this->userCrms["Sau.BTH"] = ["id" => 269, "name"  => "Bùi Thị Hai Sáu"];
        $this->userCrms["Tien.NTC"] = ["id" => 269, "name"  => "Nguyễn Thị Cẩm Tiên"];
        $this->userCrms["Trinh.HTD"] = ["id" => 269, "name"  => "Huỳnh Thị Diệu Trinh"];
        $this->userCrms["Phung.HT"] = ["id" => 269, "name"  => "Hồ Tôn Phụng"];
        $this->userCrms["cskh.leader1"] = ["id" => 269, "name"  => "Chị Minh"];
        $this->userCrms["Duyen.NCK"] = ["id" => 269, "name"  => "CSKH - Nguyễn Cao Kim Duyên"];
        $this->userCrms["CSKH.MKT1-01"] = ["id" => 269, "name"  => "Phúc Tâm - CSKH MKT1"];
        $this->userCrms["CSKH.MKT1-02"] = ["id" => 269, "name"  => "Hoàng Lộc - CSKH MKT1"];
        $this->userCrms["CSKH.MKT2"] = ["id" => 269, "name"  => "Thanh Ngân - CSKH MKT2"];
        $this->userCrms["CSKH.MKT2-01"] = ["id" => 269, "name"  => "Hồng Nhung - CSKH MKT2"];
        $this->userCrms["CSKH.MKT2-02"] = ["id" => 269, "name"  => "Nhũ Hương - CSKH MKT2"];
        $this->userCrms["CSKH.MKT1-03"] = ["id" => 269, "name"  => "Như Ý - CSKH MKT1"];
        // htp.tmdt
        $this->userCrms["online.sacngockhang@gmail.com"] = ["id" => 270, "name"  => "Phạm Dũng"];
        $this->userCrms["Phuong.NTT"] = ["id" => 270, "name"  => "Nguyễn Thị Trúc Phương"];
        $this->userCrms["Oanh.DTN"] = ["id" => 270, "name"  => "Đỗ Thị Ngọc Oanh"];
        $this->userCrms["Hien.TTD"] = ["id" => 270, "name"  => "Trần Thị Diệu Hiền"];
        $this->userCrms["Nhu.HTT"] = ["id" => 270, "name"  => "Huỳnh Trần Thảo Như"];
        $this->userCrms["Nhu.NTH"] = ["id" => 270, "name"  => "Nguyễn Thị Huỳnh Như"];
        $this->userCrms["nguyet.tmdt"] = ["id" => 270, "name"  => "Nguyệt CSKH"];
        $this->userCrms["hanh.tmdt"] = ["id" => 270, "name"  => "Hạnh CSKH"];
        $this->userCrms["thuy.tmdt"] = ["id" => 270, "name"  => "Thúy CSKH"];
        */
    }

    public function __construct(
        PxtAuthentication  $entityPxtAuthentication,
        Settings           $entitySettings,
        Products           $entityProducts,
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
        // \Zend\Debug\Debug::dump(\Admin\Service\Promotion::returnSource());
        // die();
        $view = new ViewModel();
        // check import
        $id = (int)$this->params()->fromRoute('id', 0);
        //echo $id ; die();
        $valueCurrent = $this->entityWinnerPromotions->fetchRow($id);
        if (empty($valueCurrent)) {
            die('Not found!');
        }
        // call iframe
        $this->layout()->setTemplate('iframe/layout');

        $form = new IndexForm();
        // Lấy danh sách tài khoản
        $optionUsers = $this->entityUsers->fetchAllOptions();

        $form->get('user_input')->setValueOptions(
            [
                '' => '--- Chọn một người phụ trách ---'
            ] +
            $optionUsers
        );

        $request = $this->getRequest();
        if ($request->isPost()) {
            $valuePost = $request->getPost()->toArray();
            $view->setVariable('valuePost', $valuePost);
            $form->setData($valuePost);
            if ($form->isValid()) {
                $this->setUserCrms();
                $note1 = str_replace("\n", "<br />", $valuePost["note_1"]);
                $this->entityWinnerPromotions->updateRow($valueCurrent["id"], [
                    "status_order" => 2,
                    "user_input" => $valuePost["user_input"],
                    "user_input_id" => $valuePost["user_input_id"],
                    "user_input_name" => $valuePost["user_input_name"],
                    "source" => $valuePost["source"],
                    "note_1" => $note1,
                    "inputed_at" => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ]);
                // Cập nhật tạm thời giá trị 'status_order' để chặn form ngoài view
                $valueCurrent["status_order"] = 2;
                $this->flashMessenger()->addSuccessMessage('Chuyển thành công!');
                // Cập nhật log
                $dataLogs = [];
                if ($valueCurrent["user_input_id"] != $valuePost["user_input_id"]) {
                    $dataLogs["user_input_id"] = $valuePost["user_input_id"];
                }
                if ($valueCurrent["user_input_name"] != $valuePost["user_input_name"]) {
                    $dataLogs["user_input_name"] = $valuePost["user_input_name"];
                }
                if ($valueCurrent["user_input"] != $valuePost["user_input"]) {
                    $dataLogs["user_input"] = $valuePost["user_input"];
                }
                if ($valueCurrent["source"] != $valuePost["source"]) {
                    $dataLogs["source"] = $valuePost["source"];
                }
                if ($valueCurrent["note_1"] != $note1) {
                    $dataLogs["note_1"] = $note1;
                }
                $this->entityLogWinners->addRow([
                    "user_id" => $this->sessionContainer->id,
                    "winner_promotion_id" => $valueCurrent["id"],
                    "type" => "1",
                    "datas" => json_encode($dataLogs, JSON_HEX_APOS | JSON_UNESCAPED_UNICODE),
                    "created_at" => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ]);
            } else {
                $this->entityWinnerPromotions->updateRow($valueCurrent["id"], [
                    "note_1" => str_replace("\n", "<br />", $valuePost["note_1"])
                ]);
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        } else {
            $valueCurrent["note_1"] = str_replace("<br />", "\n", $valueCurrent["note_1"]);
            $form->setData($valueCurrent);
        }
        // Lấy danh sách chương trình khuyến mãi
        $optionPromotions = $this->entityPromotions->fetchAllOptions01();
        // Lấy danh sách các mã trúng cùng
        $arrCodeInWin = $this->entityListPromotions->fetchAllInWin([
            "phone_id" => $valueCurrent["phone_id"],
            "promotion_id" => $valueCurrent["promotion_id"],
            "number_winner" => $valueCurrent["number_winner"],
        ]);

        $view->setVariable('valueCurrent', $valueCurrent);
        $view->setVariable('id', $id);
        $view->setVariable('form', $form);
        $view->setVariable('optionPromotions', $optionPromotions);
        $view->setVariable('arrCodeInWin', $arrCodeInWin);
        $view->setVariable('optionUsers', $optionUsers);
        return $view;
    }

    public function inputAction()
    {
        $plusScore = $this->entityPlusScore->fetchAlls();
        // check import
        $id = (int)$this->params()->fromRoute('id', 0);
        //echo $id ; die();
        $valueCurrent = $this->entityWinnerPromotions->fetchRow($id);
        if (empty($valueCurrent)) {
            die('Not found!');
        }
        // call iframe
        $this->layout()->setTemplate('iframe/layout');

        $form = new InputForm();
        // Lấy danh sách sản phẩm
        $optionProducts = $this->entityProducts->fetchAllOptions04();
        // Danh sách sản phẩm trúng thưởng
        $arrPromotionsProducts = $this->entityPromotionsProducts->fetchAllAsPromotionId($valueCurrent["promotion_id"]);
        // \Zend\Debug\Debug::dump($optionProducts);
        // die();
        // Thay lại option product
        $optionProductFinished = [];
        $plusScoreOption = [];
        foreach ($arrPromotionsProducts as $key => $item) {
            $optionProductFinished[$item['product_id']] = !empty($item['name']) ? $item['name'] : "Không xác định";
        }
        foreach ($plusScore as $key => $item) {
            if ($valueCurrent['score'] > $item['score']) continue;
            $plusScoreOption[$item['id']] = $item['message_win'];
        }

        $form->get('product_id')->setValueOptions(
            [
                '' => '--- Chọn một sản phẩm ---'
            ] +
            $optionProductFinished
        );
        $form->get('plusScoreId')->setValueOptions(
            [
                '' => '--- Chọn điểm trả thưởng ---'
            ] +
            $plusScoreOption
        );
        // Lấy danh sách tài khoản
        $optionUsers = $this->entityUsers->fetchAllOptions();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            $isValid = 0;
            if (isset($valuePost["is_finish"]) && $valuePost["is_finish"] == 1) {
                if (!$form->isValid()) {
                    $isValid = 1;
                }
            }
            if ($isValid == 0) {
                $plusScoreItem = $this->entityPlusScore->firstOnly($valuePost["plusScoreId"]);
                $dataUpdate = [];
                if (isset($valuePost["is_finish"]) && $valuePost["is_finish"] == 1) {
                    $dataUpdate["status_order"] = 3;
                    // Cập nhật tạm thời giá trị 'status_order' để chặn form ngoài view
                    $valueCurrent["status_order"] = 3;
                }
                $note2 = str_replace("\n", "<br />", $valuePost["note_2"]);
                $dataUpdate["phone_recipient"] = $valuePost["phone_recipient"];
                $dataUpdate["fullname_recipient"] = $valuePost["fullname_recipient"];
                $dataUpdate["address_recipient"] = $valuePost["address_recipient"];
                $dataUpdate["note_2"] = $note2;
                $dataUpdate["product_id"] = $valuePost["product_id"];
                $dataUpdate["rewarded_at"] = \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent();
                $this->entityWinnerPromotions->updateRow($valueCurrent["id"], $dataUpdate);
                // Cập nhật log
                $dataLogs = [];
                if ($valueCurrent["phone_recipient"] != $valuePost["phone_recipient"]) {
                    $dataLogs["phone_recipient"] = $valuePost["phone_recipient"];
                }
                if ($valueCurrent["fullname_recipient"] != $valuePost["fullname_recipient"]) {
                    $dataLogs["fullname_recipient"] = $valuePost["fullname_recipient"];
                }
                if ($valueCurrent["address_recipient"] != $valuePost["address_recipient"]) {
                    $dataLogs["address_recipient"] = $valuePost["address_recipient"];
                }
                if ($valueCurrent["note_2"] != $note2) {
                    $dataLogs["note_2"] = $note2;
                }

                if ($valueCurrent["product_id"] != $valuePost["product_id"]) {
                    $dataLogs["product_id"] = $valuePost["product_id"];
                }
                if (isset($valuePost["is_finish"]) && $valuePost["is_finish"] == 1) {
                    $type = "3";
                } else {
                    $type = "2";
                    $dataLogs["is_minus"] = 0;
                }
                if ($type == 3) {
                    $dataLogs["minus_points"] = empty($plusScoreItem) ? 0 : $plusScoreItem['score'];
                    $dataLogs["is_minus"] = 1;
                    $dataLogs["pointOld"] = $valueCurrent['score'];
                    $this->entityWinnerPromotions->updateRow($valueCurrent['id'],
                        ['score' => $valueCurrent['score'] - $plusScoreItem['score'],
                            "created_at" => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                        ]);
                }
                $this->entityLogWinners->addRow([
                    "user_id" => $this->sessionContainer->id,
                    "winner_promotion_id" => $valueCurrent["id"],
                    "type" => $type,
                    "datas" => json_encode($dataLogs, JSON_HEX_APOS | JSON_UNESCAPED_UNICODE),
                    "created_at" => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ]);
                $this->flashMessenger()->addSuccessMessage('Cập nhật thành công!');
            } else {
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        } else {
            $valueCurrent["note_2"] = str_replace("<br />", "\n", $valueCurrent["note_2"]);
            $form->setData($valueCurrent);
        }
        // Lấy danh sách chương trình khuyến mãi
        $optionPromotions = $this->entityPromotions->fetchAllOptions01();
        // Lấy danh sách các mã trúng cùng
        $arrCodeInWin = $this->entityListPromotions->fetchAllInWin([
            "phone_id" => $valueCurrent["phone_id"],
            "promotion_id" => $valueCurrent["promotion_id"],
            "number_winner" => $valueCurrent["number_winner"],
        ]);

        return new ViewModel([
            'valueCurrent' => $valueCurrent,
            'id' => $id,
            'form' => $form,
            'optionPromotions' => $optionPromotions,
            'arrCodeInWin' => $arrCodeInWin,
            'optionUsers' => $optionUsers
        ]);
    }

    public function rewardAction()
    {
        // check import
        $id = (int)$this->params()->fromRoute('id', 0);
        //echo $id ; die();
        $valueCurrent = $this->entityWinnerPromotions->fetchRow($id);
        if (empty($valueCurrent)) {
            die('Not found!');
        }
        // call iframe
        $this->layout()->setTemplate('iframe/layout');

        $form = new RewardForm();
        // Lấy danh sách tài khoản
        $optionUsers = $this->entityUsers->fetchAllOptions();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $valuePost = $request->getPost()->toArray();
            $form->setData($valuePost);
            if ($form->isValid()) {
                $dataUpdate = [];
                if ($valuePost["finished_at"] == "") {
                    $dataUpdate["finished_at"] = \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent();
                } else {
                    $finishedAt = date_create_from_format('d/m/Y H:i:s', $valuePost['finished_at']);
                    $dataUpdate["finished_at"] = date_format($finishedAt, 'Y-m-d H:i:s');
                }
                $dataUpdate["status_order"] = 1;
                $dataUpdate["code_order"] = $valuePost["code_order"];
                $note3 = str_replace("\n", "<br />", $valuePost["note_3"]);
                $dataUpdate["note_3"] = $note3;
                $this->entityWinnerPromotions->updateRow($valueCurrent["id"], $dataUpdate);
                // Cập nhật tạm thời giá trị 'status_order' để chặn form ngoài view
                $valueCurrent["status_order"] = 1;
                // Cập nhật log
                $dataLogs = [];
                if ($valueCurrent["code_order"] != $valuePost["code_order"]) {
                    $dataLogs["code_order"] = $valuePost["code_order"];
                }
                if ($valueCurrent["finished_at"] != $valuePost["finished_at"]) {
                    $dataLogs["finished_at"] = $valuePost["finished_at"];
                }
                if ($valueCurrent["note_3"] != $note3) {
                    $dataLogs["note_3"] = $note3;
                }
                $this->entityLogWinners->addRow([
                    "user_id" => $this->sessionContainer->id,
                    "winner_promotion_id" => $valueCurrent["id"],
                    "type" => "5",
                    "datas" => json_encode($dataLogs, JSON_HEX_APOS | JSON_UNESCAPED_UNICODE),
                    "created_at" => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ]);
                $this->flashMessenger()->addSuccessMessage('Cập nhật thành công!');
            } else {
                $this->flashMessenger()->addWarningMessage('Lỗi nhập dữ liệu, đề nghị kiểm tra lại!');
            }
        } else {
            $valueCurrent["note_3"] = str_replace("<br />", "\n", $valueCurrent["note_3"]);
            $form->setData($valueCurrent);
        }
        // Lấy danh sách chương trình khuyến mãi
        $optionPromotions = $this->entityPromotions->fetchAllOptions01();
        // Lấy danh sách các mã trúng cùng
        $arrCodeInWin = $this->entityListPromotions->fetchAllInWin([
            "phone_id" => $valueCurrent["phone_id"],
            "promotion_id" => $valueCurrent["promotion_id"],
            "number_winner" => $valueCurrent["number_winner"],
        ]);

        // Lấy danh sách sản phẩm
        $optionProducts = $this->entityProducts->fetchAllOptions01();

        return new ViewModel([
            'valueCurrent' => $valueCurrent,
            'id' => $id,
            'form' => $form,
            'optionPromotions' => $optionPromotions,
            'arrCodeInWin' => $arrCodeInWin,
            'optionUsers' => $optionUsers,
            'optionProducts' => $optionProducts
        ]);
    }

    public function finishedAction()
    {
        // check import
        $id = (int)$this->params()->fromRoute('id', 0);
        //echo $id ; die();
        $valueCurrent = $this->entityWinnerPromotions->fetchRow($id);
        if (empty($valueCurrent)) {
            die('Not found!');
        }
        // call iframe
        $this->layout()->setTemplate('iframe/layout');

        // Lấy danh sách tài khoản
        $optionUsers = $this->entityUsers->fetchAllOptions();

        $form = new FinishedForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($this->sessionContainer->id == '268') {
                $dataUpdate["status_order"] = 2;
                $this->entityWinnerPromotions->updateRow($valueCurrent["id"], $dataUpdate);
                // Cập nhật tạm thời giá trị 'status_order' để chặn form ngoài view
                $this->flashMessenger()->addSuccessMessage('Hoàn đơn thành công!');
                $valueCurrent = $this->entityWinnerPromotions->fetchRow($id);
                // Cập nhật log
                $dataLogs = [];
                $this->entityLogWinners->addRow([
                    "user_id" => $this->sessionContainer->id,
                    "winner_promotion_id" => $valueCurrent["id"],
                    "type" => "6",
                    "datas" => json_encode($dataLogs, JSON_HEX_APOS | JSON_UNESCAPED_UNICODE),
                    "created_at" => \Pxt\Datetime\ChangeDatetime::getDatetimeCurrent()
                ]);
            } else {
                $this->flashMessenger()->addWarningMessage('Tài khoản không được phép thao tác hoàn đơn!');
            }
        }

        // Lấy danh sách chương trình khuyến mãi
        $optionPromotions = $this->entityPromotions->fetchAllOptions01();
        // Lấy danh sách các mã trúng cùng
        $arrCodeInWin = $this->entityListPromotions->fetchAllInWin([
            "phone_id" => $valueCurrent["phone_id"],
            "promotion_id" => $valueCurrent["promotion_id"],
            "number_winner" => $valueCurrent["number_winner"],
        ]);

        // Lấy danh sách sản phẩm
        $optionProducts = $this->entityProducts->fetchAllOptions01();

        return new ViewModel([
            'valueCurrent' => $valueCurrent,
            'id' => $id,
            'optionPromotions' => $optionPromotions,
            'arrCodeInWin' => $arrCodeInWin,
            'optionUsers' => $optionUsers,
            'form' => $form,
            'optionProducts' => $optionProducts
        ]);
    }

    public function getPhoneAction()
    {
        $this->setUserCrms();

        // kết quả trả ra
        $responses = [
            "status" => 0,
            "message" => "Có lỗi trong quá trình xử lý, liên hệ fortune để biết thêm thông tin!"
        ];
        // Id ở đây chính là phone_id
        $id = (int)$this->params()->fromRoute('id', 0);
        $id = "delete" . $id;
        $phone = str_replace("delete84", "0", $id);

        $url = "https://sacngockhang.getflycrm.com/api/v3/accounts?limit=1&page=1&q=" . $phone;
        libxml_use_internal_errors(true); // tắt thông báo lỗi khi đọc file
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:text/html; charset=utf-8', 'X-API-KEY:Kd6zSR4FRxPz5D2C2QZh8AKZMLccWy'));
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        $results = json_decode($result, true);
        // \Zend\Debug\Debug::dump($results);
        if (isset($results["records"][0]["manager_user_name"])) {
            $user = $results["records"][0]["manager_user_name"];
            if (isset($this->userCrms[$user]["id"])) {
                $responses["status"] = 1;
                $responses["user_input"] = $this->userCrms[$user]["id"];
                $responses["user_input_id"] = $user;
                $responses["user_input_name"] = $this->userCrms[$user]["name"];
            } else {
                $responses["message"] = "Người phụ trách '" . $user . "' không tìm thấy trong danh sách của fortune, liên hệ fortune để cập nhật!";
            }
        } else {
            $responses["message"] = "Không tìm thấy người phụ trách của số điện thoại này trên CRM!";
        }
        echo json_encode($responses);
        die();
    }
}
