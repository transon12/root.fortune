
    <!-- toast CSS -->
    <link href="<?= TEMPS ?>assets/toast-master/css/jquery.toast.css" rel="stylesheet">
    <!-- page css -->
    <link href="<?= TEMPS ?>assets/toast-master/css/other-pages.css" rel="stylesheet">

    <script src="<?= TEMPS ?>assets/toast-master/js/jquery.toast.js"></script>
    <script src="<?= TEMPS ?>assets/toast-master/js/toastr.js"></script>
    <script type="text/javascript">
    $(function() {
        <?php 
            // Check flash message
            $titleFlashMessage = '';
            $contentFlashMessage = '';
            if($this->flashMessenger()->renderCurrent('success')){
                $titleFlashMessage = 'Thông báo thành công';
                $contentFlashMessage = $this->flashMessenger()->setMessageOpenFormat('')->setMessageSeparatorString('')->setMessageCloseString('')->renderCurrent('success');
                $icon = 'success';
            }elseif($this->flashMessenger()->renderCurrent('warning')){
                $titleFlashMessage = 'Cảnh báo';
                $contentFlashMessage = $this->flashMessenger()->setMessageOpenFormat('')->setMessageSeparatorString('')->setMessageCloseString('')->renderCurrent('warning');
                $icon = 'warning';
            }elseif($this->flashMessenger()->renderCurrent('error') || isset($this->flashError)){
                $titleFlashMessage = 'Thông báo lỗi';
                $contentFlashMessage = $this->flashMessenger()->setMessageOpenFormat('')->setMessageSeparatorString('')->setMessageCloseString('')->renderCurrent('error');
                $icon = 'error';
            }elseif($this->flashMessenger()->renderCurrent('info') || isset($this->flashInfo)){
                $titleFlashMessage = 'Thông tin';
                $contentFlashMessage = $this->flashMessenger()->setMessageOpenFormat('')->setMessageSeparatorString('')->setMessageCloseString('')->renderCurrent('info');
                $icon = 'info';
            }elseif($this->flashMessenger()->renderCurrent('default') || isset($this->flashDefault)){
                $titleFlashMessage = 'Thông báo mặc định';
                $contentFlashMessage = $this->flashMessenger()->setMessageOpenFormat('')->setMessageSeparatorString('')->setMessageCloseString('')->renderCurrent('default');
                $icon = 'info';
            }
            // clear flash message
            $this->flashMessenger()->clearCurrentMessagesFromContainer();
            if($titleFlashMessage != ''){
                $str = "$.toast({";
                $str .= "heading: '" . $titleFlashMessage . "',";
                $str .= "text: '" . $contentFlashMessage . "',";
                $str .= "position: 'top-right',";
                $str .= "loaderBg:'#ff6849',";
                $str .= "icon: '" . $icon . "',";
                $str .= "hideAfter: 6000,";
                $str .= "stack: 6";
                $str .= "});";
                echo $str;
            }
        ?>
    })
	</script>