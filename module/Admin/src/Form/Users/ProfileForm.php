<?php

namespace Admin\Form\Users;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Identical;
use Zend\Validator\File\Size;
use Zend\Validator\File\MimeType;

class ProfileForm extends Form{
    
    public function __construct() {
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data'
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){
        $firstname = new Element\Text('firstname');
        $firstname->setLabel('Họ & tên đệm: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Họ & tên đệm: '
            ]);
        $this->add($firstname);
        
        $lastname = new Element\Text('lastname');
        $lastname->setLabel('Tên: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Tên: '
            ]);
        $this->add($lastname);
        
        $avatar = new Element\File('avatar');
        $avatar->setLabel('Hình đại diện: ')
            ->setAttributes([
                'id' => 'avatar'
            ]);
        $this->add($avatar);

        $password = new Element\Password('password');
        $password->setLabel('Mật khẩu: ')
        ->setAttributes([
            'class' => 'form-control',
            'placeholder'   => 'Mật khẩu: '
        ]);
        $this->add($password);

        $rePassword = new Element\Password('re_password');
        $rePassword->setLabel('Nhập lại mật khẩu: ')
        ->setAttributes([
            'class' => 'form-control',
            'placeholder'   => 'Nhập lại mật khẩu: '
        ]);
        $this->add($rePassword);
        
        $gender = new Element\Radio('gender');
        $gender->setLabel('Giới tính: ')
            ->setAttributes([
                'value'     => '0',
                'class'     => 'radio-col-brown',
                'style'     => 'margin-left: 10px; margin-right: 3px;'
            ])
            ->setValueOptions(
                [
                'female' => [
                    'label' => 'Nữ', 
                    'label_attributes' => ['for' => 'female'], 
                    'value' => '0', 
                    'attributes' => ['id' => 'female']
                ],
                'male' => [
                    'label' => 'Nam', 
                    'label_attributes' => ['for' => 'male'], 
                    'value' => '1', 
                    'attributes' => ['id' => 'male']
                ]
            ]);
        $this->add($gender);
        
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
            'name' => 'firstname',
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
                            NotEmpty::IS_EMPTY => "Họ không được để trống!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 1,
                        'max' => 128,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui long nhap duoi %max% ky tu",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ky tu"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'lastname',
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
                            NotEmpty::IS_EMPTY => "Tên không được để trống!"
                        ]
                    ]
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 1,
                        'max' => 128,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui long nhap duoi %max% ky tu",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ky tu"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'password',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 0,
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
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [[
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
        
        $inputFilter->add([
            'type'     => 'Zend\InputFilter\FileInput',
            'name'     => 'avatar',
            'required' => false,
            'validators' => [[
                'name'    => 'Zend\Validator\File\MimeType',
                'options' => [
                    'mimeType'  => [
                        'image/jpeg', 'image/png', 'image/jpg', 'image/gif'
                    ],
                    'messages' => [
                        MimeType::FALSE_TYPE => "Định dạng %type% không cho phép!",
                        MimeType::NOT_DETECTED => "Mimetype không xác định",
                        MimeType::NOT_READABLE => "Mimetype không thể đọc"
                    ]
                ]
            ],[
                'name'    => 'FileSize',
                'options' => [
                    'max'  => 1024*10000,
                    'messages' => [
                        Size::TOO_BIG => "File quá lớn, chỉ cho phép file dưới %max%"
                    ]
                ]
            ],
            ]
        ]);
        
    }
}