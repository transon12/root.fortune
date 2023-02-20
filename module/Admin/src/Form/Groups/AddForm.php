<?php

namespace Admin\Form\Groups;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Digits;

class AddForm extends Form{

    private $userId;
    
    public function __construct($route = '', $userId = null) {
        $this->userId = $userId;
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
        
        if($this->userId == '1'){
            $companyId = new Element\Select('company_id');
            $companyId->setLabel('Công ty: ')
                ->setAttributes([
                    'id' => 'company_id',
                    'class' => 'form-control',
                    'placeholder'   => 'Công ty: '
                ]);
            $this->add($companyId);
        }
        
        $name = new Element\Text('name');
        $name->setLabel('Tên bộ phận: ')
            ->setLabelAttributes([
            ])
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Tên bộ phận: '
            ]);
        $this->add($name);
        
        $level = new Element\Text('level');
        $level->setLabel('Cấp độ: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Cấp độ: ',
                'value' => '1'
            ]);
        $this->add($level);
        
        $description = new Element\Textarea('description');
        $description->setLabel('Mô tả thông tin: ')
            ->setLabelAttributes([
//                 'class' => 'col-md-12 form-group'
            ])
            ->setAttributes([
                'rows'       => 5,
                'id' => 'description',
                'class' => 'form-control',
                'placeholder'   => 'Mô tả thông tin: '
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
            ->setLabelAttributes([
//                 'class' => 'col-md-12 control-lable'
            ])
            ->setAttributes([
//                 'class' => 'col-md-12',
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
                            NotEmpty::IS_EMPTY => "Tên không được để trống!"
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
            'name' => 'level',
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
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 3,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
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
        
    }
}