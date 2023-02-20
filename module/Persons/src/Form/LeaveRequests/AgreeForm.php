<?php

namespace Persons\Form\LeaveRequests;

use Zend\Form\Form;

class AgreeForm extends Form{
    public function __construct($route = ''){
        parent::__construct();

        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form-modal',
            'route' => $route,
            'enctype' => 'multipart/form-data',
            'data-target' => '#defaultSize',
        ]);
        
        $this->setElements();
    }

    private function setElements(){
        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Xác nhận'
            ]
        ]);
    }
}