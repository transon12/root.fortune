<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Admin\Form\Users;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Identical;

class EditForm extends Form{
    
    private $action;
    private $userId;
    
    public function __construct($action = 'edit', $userId = null) {
        $this->action = $action;
        $this->userId = $userId;
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
        
        if($this->userId == '1'){
            $companyId = new Element\Select('company_id');
            $companyId->setLabel('Công ty: ')
                ->setAttributes([
                    'id' => 'company_id',
                    'class' => 'form-control',
                    'placeholder'   => 'Công ty: ',
                    'disabled' => true
                ]);
            $this->add($companyId);
        }
        
        $username = new Element\Text('username');
        $username->setLabel('Tên đăng nhập: ')
            ->setLabelAttributes([
//                 'class' => 'col-md-12 form-group'
            ])
            ->setAttributes([
                'id' => 'username',
                'class' => 'form-control',
                'placeholder'   => 'Tên đăng nhập: ',
                'readonly' => true
            ]);
        $this->add($username);
        
        $firstname = new Element\Text('firstname');
        $firstname->setLabel('Họ & tên đệm: ')
            ->setLabelAttributes([
//                 'class' => 'col-md-12 form-group'
            ])
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Họ & tên đệm: '
            ]);
        $this->add($firstname);
        
        $lastname = new Element\Text('lastname');
        $lastname->setLabel('Tên: ')
            ->setLabelAttributes([
//                 'class' => 'col-md-12 control-lable'
            ])
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Tên: '
            ]);
        $this->add($lastname);
        
        $gender = new Element\Radio('gender');
        $gender->setLabel('Giới tính: ')
            ->setLabelAttributes([
//                 'class' => 'col-md-12 control-lable'
            ])
            ->setAttributes([
//                 'class' => 'col-md-12',
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
//                 '0'   => 'Chưa được kích hoạt',
//                 '1'    => 'Đã được kích hoạt',
//                 '2'    => 'Đã bị khóa'
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

        if($this->userId == '1'){
            $inputFilter->add([
                'name' => 'company_id',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags']
                ],
                // 'validators' => [[
                //     'name' => 'NotEmpty',
                //     'break_chain_on_failure' => true,
                //     'options' => [
                //         'messages' => [
                //             NotEmpty::IS_EMPTY => "Phải chọn một công ty!"
                //         ]
                //     ]
                // ],]
            ]);
        }
        
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
        
        
    }
}