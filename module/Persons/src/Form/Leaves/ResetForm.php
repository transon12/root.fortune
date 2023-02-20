<?php

namespace Persons\Form\Leaves;

use Zend\Form\Form;

class ResetForm extends Form{
    public function __construct(){
        parent::__construct();

        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data',
            'method' => 'POST'
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