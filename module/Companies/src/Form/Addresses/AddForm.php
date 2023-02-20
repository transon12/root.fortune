<?php

namespace Companies\Form\Addresses;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

class AddForm extends Form{

    public function __construct($route=''){
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

        $isType = new Element\Select('is_type');
        $isType->setLabel('Loại: ')
            ->setAttributes([
                'value' => '1',
                'id' => 'is_type',
                'class' => 'form-control'
            ])
            ->setValueOptions([
                "0" => "Trụ sở chính",
                "1" => "Chi nhánh",
                "2" => "Nhà máy"
            ]);
        $this->add($isType);

        $address = new Element\Text('address');
        $address->setLabel('Địa chỉ: ')
            ->setAttributes([
                'id' => 'address',
                'class' => 'form-control',
                'placeholder' => 'Địa chỉ' 
            ]);
        $this->add($address);

        $phone = new Element\Text('phone');
        $phone->setLabel('Số điện thoại: ')
            ->setAttributes([
                'id' => 'address',
                'class' => 'form-control',
                'placeholder' => 'số điện thoại' 
            ]);
        $this->add($phone);

        $status = new Element\Radio('status');
        $status->setLabel('Trạng thái: ')
            ->setAttributes([
                'value'     => '1',
                'class'     => 'radio-col-brown',
                'style'     => 'margin-left: 10px; margin-right: 3px;'
            ])
            ->setValueOptions([
                'yes' => [
                    'label' => 'Hoạt động', 
                    'label_attributes' => ['for' => 'yes'], 
                    'value' => '1', 
                    'attributes' => ['id' => 'yes']
                ],'no' => [
                    'label' => 'Không hoạt động', 
                    'label_attributes' => ['for' => 'no'], 
                    'value' => '0', 
                    'attributes' => ['id' => 'no']
                ]
            ]);
        $this->add($status);

        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Lưu'
            ]
        ]);
    }

    public function validatorForm(){
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
            'name' => 'is_type',
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
                            NotEmpty::IS_EMPTY => "Phải chọn một một giá trị"
                        ]
                    ]
                ],
            ]
        ]);

        $inputFilter->add([
            'name' => 'phone',
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
                            NotEmpty::IS_EMPTY => "Không được để trống"
                        ]
                    ]
                ],
            ]
        ]);
    }
}