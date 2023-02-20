<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Admin\Form\Index;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Identical;
use Zend\Validator\Regex;

class LoginForm extends Form{
    
    private $action;
    
    public function __construct() {
        parent::__construct();
        $this->setAttributes([
            'class' => 'form-horizontal form-simple',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data'
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){
        $username = new Element\Text('username');
        $username->setLabel('Tên đăng nhập: ')
            ->setLabelAttributes([
//                 'class' => 'col-md-12 form-group'
            ])
            ->setAttributes([
                'id' => 'username',
                'class' => 'form-control',
                'placeholder'   => 'Tên đăng nhập: '
            ]);
        $this->add($username);

        $password = new Element\Password('password');
        $password->setLabel('Mật khẩu: ')
        ->setLabelAttributes([
//             'class' => 'col-md-12 control-lable'
        ])
        ->setAttributes([
            'id' => 'password',
            'class' => 'form-control',
            'placeholder'   => 'Mật khẩu: '
        ]);
        $this->add($password);
        
        $rememberMe = new Element\Checkbox('remember_me');
        $rememberMe->setLabel('Ghi nhớ')
                ->setAttributes([
                    'id'    => 'remember_me'
                ]);
        $rememberMe->setAttributes([
            'class' => 'chk-remember',
            'id'    => 'remember_me',
            'checked'   => false
        ]);
        $this->add($rememberMe);
        
        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-info btn-block',
                'value' => 'Đăng nhập'
            ]
        ]);
    }
    
    private function validatorForm(){
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        
        $inputFilter->add([
            'name' => 'username',
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
                            NotEmpty::IS_EMPTY => "Tên đăng nhập không được để trống!"
                        ]
                    ]
                ],
                [
                    'name' => 'Regex',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'pattern' => '/^[a-zA-Z0-9_.-]*$/u',
                        'messages' => [
                            Regex::NOT_MATCH => "Tên đăng nhập hoặc mật khẩu không chính xác"
                        ]
                    ]
                ]
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'password',
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
                            NotEmpty::IS_EMPTY => "Mật khẩu không được để trống!"
                        ]
                    ]
                ],
            ]
        ]);
        
    }
}