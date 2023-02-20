<?php

namespace DataApi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Admin\Model\PxtAuthentication;
use DataApi\Core\DataApiCore;

class IndexController extends DataApiCore{
    
    public function __construct(PxtAuthentication $entityPxtAuthentication) {
        parent::__construct($entityPxtAuthentication);
    }
    
    public function indexAction(){
		  die("DataApi / index / index");
    }
}
