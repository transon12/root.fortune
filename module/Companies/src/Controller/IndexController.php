<?php

namespace Companies\Controller;

use Admin\Core\AdminCore;
use Zend\View\Model\ViewModel;

class IndexController extends AdminCore{

    public function indexAction(){
        return new ViewModel();
    }
}