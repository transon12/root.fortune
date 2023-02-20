<?php

namespace Settings\Form\Technologies;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class AddForm extends Form{

    public function __construct($route=''){
        parent::__construct();

        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form-modal',
            'route' => $route,
            'enctype' => 'multipart/form-data',
            'data-target' => '#defaultSize'
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }

    private function setElements(){
        $name = new Element\Text('name');
        $name->setLabel('Tên công nghệ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder' => 'Tên công nghệ'
            ]);
        $this->add($name);

        $description = new Element\Text('description');
        $description->setLabel('Mô tả: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder' => 'Mô tả'
            ]);
        $this->add($description);

        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Lưu'
            ]
        ]);

        $status = new Element\Radio('status');
        $status->setLabel('Trạng thái: ')
            ->setAttributes([
                'value' => '1',
                'class' => 'radio-col-brown',
                'style' => 'margin-left: 10px; margin-right: 3px;'
            ])
            ->setValueOptions([
                'yes' => [
                    'label' => 'Hoạt động',
                    'label_attributes' => ['for' => 'yes'], 
                    'value' => '1', 
                    'attributes' => ['id' => 'yes']
                ],
                'no' => [
                    'label' => 'Không hoạt động', 
                    'label_attributes' => ['for' => 'no'], 
                    'value' => '0', 
                    'attributes' => ['id' => 'no']
                ]
            ]);
        $this->add($status);
    }

    private function validatorForm(){
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
            'name' => 'name',
            'required' => true,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => "Không được để trống!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 2,
                        'max' => 128,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui long nhap duoi %max% ky tu",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ky tu"
                        ]
                    ]
                ],
            ]
        ]);
    }
}