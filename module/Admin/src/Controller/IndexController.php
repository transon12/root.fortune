<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;

class IndexController extends AdminCore{
    public $entityUsers;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication) {
        parent::__construct($entityPxtAuthentication);
//         parent::__construct();
    }
    
    public function indexAction(){
        //die('abc');
        return new ViewModel();
    }
        
}
