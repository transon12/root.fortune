<?php

namespace Storehouses\Form\Exports;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;

class ExportsForm extends Form{
    
    public function __construct() {
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data',
            'method' => 'POST'
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){

        $status = new Element\Select('status');
        $status->setLabel('Trạng thái: ')
        ->setAttributes([
            'id' => 'status',
            'class' => 'form-control'
        ])
        ->setValueOptions(["1" => "Xuất kho", "0" => "Nhập trả lại"]);
        $this->add($status);
        
        $codes = new Element\Text('codes');
        $codes->setLabel('Số serial hoặc qrcode: ')
            ->setAttributes([
                'id' => 'codes',
                'class' => 'form-control',
                'placeholder'   => 'Số serial hoặc qrcode'
            ]);
        $this->add($codes);
        
        $exportedAt = new Element\Text('exported_at');
        $exportedAt->setLabel('Thời gian xuất: ')
            ->setAttributes([
                'id' => 'exported_at',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Thời gian xuất: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($exportedAt);
        
        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Thực thi'
            ]
        ]);
    }
    
    private function validatorForm(){
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        
        $inputFilter->add([
            'name' => 'codes',
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
                            NotEmpty::IS_EMPTY => "Không được để trống!"
                        ]
                    ]
                ],
            ]
        ]);
        
        /* $inputFilter->add([
            'name' => 'exported_at',
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
                            NotEmpty::IS_EMPTY => "Không được để trống!"
                        ]
                    ]
                ],
            ]
        ]); */
        
    }
}