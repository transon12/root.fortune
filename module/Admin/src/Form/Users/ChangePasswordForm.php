<?php

namespace Admin\Form\Users;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Identical;

class ChangePasswordForm extends Form{
    
    public function __construct($route = '') {
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

        $password = new Element\Password('password');
        $password->setLabel('Mật khẩu: ')
        ->setLabelAttributes([
//             'class' => 'col-md-12 control-lable'
        ])
        ->setAttributes([
            'class' => 'form-control',
            'placeholder'   => 'Mật khẩu: '
        ]);
        $this->add($password);

        $rePassword = new Element\Password('re_password');
        $rePassword->setLabel('Nhập lại mật khẩu: ')
        ->setLabelAttributes([
//             'class' => 'col-md-12 control-lable'
        ])
        ->setAttributes([
            'class' => 'form-control',
            'placeholder'   => 'Nhập lại mật khẩu: '
        ]);
        $this->add($rePassword);
        
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
            'name' => 'password',
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
                            NotEmpty::IS_EMPTY => "Mật khẩu không được để trống!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 6,
                        'max' => 64,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui long nhap duoi %max% ky tu",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ky tu"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 're_password',
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
                            NotEmpty::IS_EMPTY => "Nhập lại mật khẩu không được để trống!"
                        ]
                    ]
                ],[
                    'name' => 'Identical',
                    'options' => [
                        'break_chain_on_failure' => true,
                        'token' => 'password',
                        'messages' => [
                            Identical::NOT_SAME => "Mật khẩu không giống nhau!",
                            Identical::MISSING_TOKEN => "Missing token"
                        ]
                    ]
                ]
            ]
        ]);
        
    }
}