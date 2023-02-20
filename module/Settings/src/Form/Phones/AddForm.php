<?php

namespace Settings\Form\Phones;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Digits;

class AddForm extends Form{
    
    public function __construct($route = '') {
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form-modal',
            'route' => $route,
            'enctype' => 'multipart/form-data'
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){
        $agentsId = new Element\Select('agents_id');
        $agentsId->setLabel('Đại lý: ')
            ->setAttributes([
                'class' => 'form-control'
            ]);
        $this->add($agentsId);
        
        $id = new Element\Text('id');
        $id->setLabel('Số điện thoại: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Số điện thoại: '
            ]);
        $this->add($id);
        
        $fullname = new Element\Text('fullname');
        $fullname->setLabel('Họ và tên: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Họ và tên: '
            ]);
        $this->add($fullname);
        
        $address = new Element\Text('address');
        $address->setLabel('Địa chỉ: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Địa chỉ: '
            ]);
        $this->add($address);

        $lockTime = new Element\Text('lock_time');
        $lockTime->setLabel('Thời gian chặn (tính theo giờ): ')
        ->setAttributes([
            'class' => 'form-control',
            'value' => '0',
            'placeholder'   => 'Thời gian chặn (tính theo giờ): '
        ]);
        $this->add($lockTime);
        
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
            'name' => 'agents_id',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'id',
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
                            NotEmpty::IS_EMPTY => "Số điện thoại không được để trống!"
                        ]
                    ]
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 10,
                        'max' => 12,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui long nhap duoi %max% ky tu",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ky tu"
                        ]
                    ]
                ],[
                    'name' => 'Digits',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'messages' => [
                            Digits::NOT_DIGITS => "Bắt buộc phải là số!"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'lock_time',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [[
                    'name' => 'Digits',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'messages' => [
                            Digits::NOT_DIGITS => "Bắt buộc phải là số!"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'fullname',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
        ]);
        
        $inputFilter->add([
            'name' => 'address',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
        ]);
        
    }
}