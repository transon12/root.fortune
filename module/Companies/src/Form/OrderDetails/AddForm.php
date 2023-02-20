<?php

namespace Companies\Form\OrderDetails;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

class AddForm extends Form{

    public function __construct(){
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data',
           
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }

    private function setElements(){
        $technologiesId = new Element\Select('technologies_id');
        $technologiesId->setLabel('Công nghệ trên tem: ')
            ->setAttributes([
                'class' => 'form-control',
            ]);
        $this->add($technologiesId);
        
        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Lưu'
            ]
        ]);
    }

    private function validatorForm(){
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        
        $inputFilter->add([
            'name' => 'technologies_id',
            'required' => true,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [[
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => "Công nghệ không được để trống!"
                        ]
                    ]
                ],
            ]
        ]);
    }
}