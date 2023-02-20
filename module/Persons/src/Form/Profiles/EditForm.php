<?php

namespace Persons\Form\Profiles;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\EmailAddress;
use Zend\Validator\File\Extension;
use Zend\Validator\File\MimeType;
use Zend\Validator\File\Size;
use Zend\Validator\Identical;
use Zend\Validator\NotEmpty;
use Zend\Validator\Regex;
use Zend\Validator\StringLength;

class EditForm extends Form{
    private $action;
    
    public function __construct($action = null, $settingDatas = null){
        $this->action = $action;
        $this->settingDatas = $settingDatas;
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
        $firstname = new Element\Text('firstname');
        $firstname->setLabel('Tên: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Tên: ',
            ]);
        $this->add($firstname);
        
        $lastname = new Element\Text('lastname');
        $lastname->setLabel('Họ & tên đệm: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Họ & tên đệm: '
            ]);
        $this->add($lastname);
        
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

        $birthday = new Element\Text('birthday');
        $birthday->setLabel('Ngày tháng năm sinh: ')
                ->setAttributes([
                    'id' => 'birthday',
                    'class' => 'form-control datetimepicker-input',
                    'placeholder'   => 'Ngày tháng năm sinh',
                    'data-toggle' => 'datetimepicker',
                ]);
        $this->add($birthday);

        $phones = new Element\Text('phones');
        $phones->setLabel('Số điện thoại:')
                ->setAttributes([
                    'class' => 'form-control',
                    'placeholder'   => 'Số điện thoại: '
                ]);
        $this->add($phones);

        $addresses = new Element\Text('addresses');
        $addresses->setLabel('Địa chỉ thường trú:')
                ->setAttributes([
                    'class' => 'form-control',
                    'placeholder'   => 'Địa chỉ thường trú: '
                ]);
        $this->add($addresses);

        $homeTown = new Element\Text('home_town');
        $homeTown->setLabel('Quê quán:')
                ->setAttributes([
                    'class' => 'form-control',
                    'placeholder'   => 'Quê quán: '
                ]);
        $this->add($homeTown);

        $identityCards = new Element\Text('identity_cards');
        $identityCards->setLabel('CCCD / CMND:')
                ->setAttributes([
                    'class' => 'form-control',
                    'placeholder'   => 'CCCD/CMND: '
                ]);
        $this->add($identityCards);

        $email = new Element\Email('email');
        $email->setLabel('Địa chỉ Email:')
                ->setAttributes([
                    'class' => 'form-control',
                    'placeholder'   => 'Địa chỉ Email: '
                ]);
        $this->add($email);

        $positionId = new Element\Select('position_id');
        $positionId->setLabel('Chức vụ:')
                ->setAttributes([
                    'class' => 'form-control',
                    'placeholder'   => 'Chức vụ: '
                ]);
        $this->add($positionId);

        $educations = new Element\Text('level_educations');
        $educations->setLabel('Trình độ học vấn:')
                ->setAttributes([
                    'class' => 'form-control',
                    'placeholder'   => 'Trình độ học vấn: '
                ]);
        $this->add($educations);

        if($this->action == "edit-profile"){
            $startDate = new Element\Text('start_date');
            $startDate->setLabel('Ngày bắt đầu làm việc: ')
                    ->setAttributes([
                        'id' => 'start_date',
                        'class' => 'form-control datetimepicker-input',
                        'placeholder'   => 'Ngày bắt đầu làm việc',
                        'data-toggle' => 'datetimepicker',
                    ]);
            $this->add($startDate);

            $stopDate = new Element\Text('stop_date');
            $stopDate->setLabel('Ngày nghỉ việc: ')
                    ->setAttributes([
                        'id' => 'stop_date',
                        'class' => 'form-control datetimepicker-input',
                        'placeholder'   => 'Ngày nghỉ việc',
                        'data-toggle' => 'datetimepicker',
                    ]);
            $this->add($stopDate);
        }

        if($this->action == "edit" || $this->action == "edit-profile"){
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

            $avatar = new Element\File('avatar');
            $avatar->setLabel('Hình đại diện: ')
                ->setAttributes([
                    'id' => 'avatar',
                ]);
            $this->add($avatar);

            $backgroundAvatar = new Element\File('background_avatar');
            $backgroundAvatar->setLabel('Hình nền: ')
                ->setAttributes([
                    'id' => 'background_avatar'
                ]);
            $this->add($backgroundAvatar);
            
            $this->add([
                'name'  => 'btnSubmit',
                'type'  => 'submit',
                'attributes'    => [
                    'class' => 'btn btn-success',
                    'value' => 'Lưu'
                ]
            ]);
        }
        if(isset($this->settingDatas) && $this->settingDatas != null){
            foreach($this->settingDatas as $key => $item){
                $data = new Element\Text($key);
                $data->setLabel($item['name'])
                ->setAttributes([
                    'class'         => 'form-control',
                    'placeholder'   => $item['name'],
                    'value'         => $item['content'],
                    'disabled'      => true,
                ]);
                $this->add($data);
            }
        }
    }

    private function validatorForm(){
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
            'name' => 'firstname',
            'required' => true,
            'filter' => [
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
            'filter' => [
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
            'filter' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 6,
                        'max' => 64,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 're_password',
            'required' => false,
            'filter' => [
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
            'name'     => 'avatar',
            'required' => false,
            'filter' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [[
                'name'    => 'Zend\Validator\File\MimeType',
                'options' => [
                    'mimeType'  => [
                        'image/jpeg', 'image/png', 'image/jpg', 'image/gif'
                    ],
                    'messages' => [
                        MimeType::FALSE_TYPE => "Chỉ được phép upload tập tin có định dạng jpeg, png, jpg, gif!",
                        MimeType::NOT_DETECTED => "Mimetype không xác định",
                        MimeType::NOT_READABLE => "Mimetype không thể đọc"
                    ]
                ]
            ],[
                'name'    => 'FileSize',
                'options' => [
                    'break_chain_on_failure' => true,
                    'max'  => 1024*10000,
                    'messages' => [
                        Size::TOO_BIG => "File quá lớn, chỉ cho phép file dưới %max%"
                    ]
                ]
            ],
            ]
        ]);

        $inputFilter->add([
            'name'     => 'background_avatar',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [[
                'name'    => 'Zend\Validator\File\MimeType',
                'options' => [
                    'mimeType'  => [
                        'image/jpeg', 'image/png', 'image/jpg', 'image/gif'
                    ],
                    'messages' => [
                        MimeType::FALSE_TYPE => "Chỉ được phép upload tập tin có định dạng jpeg, png, jpg, gif!",
                        MimeType::NOT_DETECTED => "Mimetype không xác định",
                        MimeType::NOT_READABLE => "Mimetype không thể đọc"
                    ]
                ]
            ],[
                'name'    => 'FileSize',
                'options' => [
                    'break_chain_on_failure' => true,
                    'max'  => 1024*10000,
                    'messages' => [
                        Size::TOO_BIG => "File quá lớn, chỉ cho phép file dưới %max%"
                    ]
                ]
            ],
            ]
        ]);

        $inputFilter->add([
            'name' => 'email',
            'required' => false,
            'filter'=>[
                ['name' => 'StringTrim'],
                ['name' => 'StripTags'],
                ['name' => 'StripNewlines']
            ],
            'validators' => [
                [
                    'name'=>'Regex',
                    'options' => [
                        'break_chain_on_failure' => true,
                        'pattern' => "/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/",
                        'messages' => [
                            Regex::NOT_MATCH => 'Email phải chứa các kí tự %pattern',
                           
                        ]
                    ]
                ],
                [
                    'name'=>'EmailAddress',
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            EmailAddress::INVALID_FORMAT => 'Email không đúng định dạng',
                            EmailAddress::INVALID_HOSTNAME => 'Hostname không đúng'
                        ]
                    ]

                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'position_id',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
        ]);
    }
}