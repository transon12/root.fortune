<?php

namespace Storehouses\Form\Details;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;

class ResetForm extends Form{
    
    public function __construct() {
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

        $codes = new Element\Text('codes');
        $codes->setLabel('Số serial hoặc qrcode: ')
            ->setAttributes([
                'id' => 'codes',
                'class' => 'form-control',
                'placeholder'   => 'Số serial hoặc qrcode'
            ]);
        $this->add($codes);

        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Reset'
            ]
        ]);
    }
}