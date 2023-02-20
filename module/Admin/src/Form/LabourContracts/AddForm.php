<?php

namespace Admin\Form\LabourContracts;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\File\Extension;
use Zend\Validator\File\Size;
use Zend\Validator\NotEmpty;

class AddForm extends Form{

    public function __construct($route = '') {
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form-modal',
            'enctype' => 'multipart/form-data',
            'method' => 'POST',
            'route' => $route,
            'data-target' => '#defaultSize',
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }

    private function setElements(){

        $beginedAt = new Element\Text('begined_at');
        $beginedAt->setLabel('Ngày bắt đầu')
            ->setAttributes([
                'id' => 'begined_at',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Ngày bắt đầu: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($beginedAt);

        $endedAt = new Element\Text('ended_at');
        $endedAt->setLabel('Ngày kết thúc')
            ->setAttributes([
                'id' => 'ended_at',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Ngày kết thúc: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($endedAt);

        $file = new Element\File('file');
        $file->setLabel('Chọn hợp đồng: ')
            ->setAttributes([
                "id" => "file"
            ]);
        $this->add($file);

        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Thêm'
            ]
        ]);
    }

    private function validatorForm(){
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        
        $inputFilter->add([
            'name' => 'begined_at',
            'required' => true,
            'filters' => [
                ['name' =>'StringTrim'],
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

        $inputFilter->add([
            'name' => 'file',
            'required' => false,
            'validators' => [
                [
                   // 'name' => \Zend\Validator\File\Extension::class,
                    'name' => 'Zend\Validator\File\Extension',
                    'options' => [
                    'extension' => ['jpg','png','jpeg', 'pdf'],
                    'case' => false, //không phân biệt HOA/thường
                    'messages' => [
                        Extension::FALSE_EXTENSION => "File upload không đúng định dạng"
                        ],
                    ],
                ],
                [
                    'name'    => 'FileSize',
                    'options' => [
                        'max'  => 1024*1024*10,
                        'messages' => [
                            Size::TOO_BIG => "File quá lớn, chỉ cho phép file dưới %max%"
                        ]
                    ]
                ],
                
            ]
        ]);
        
    }
}