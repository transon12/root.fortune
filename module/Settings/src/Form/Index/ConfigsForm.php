<?php

namespace Settings\Form\Index;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Digits;

class ConfigsForm extends Form{

    private $userId;
    
    public function __construct($userId = null) {
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
                    'placeholder'   => 'Công ty: '
                ]);
            $this->add($companyId);
        }
        
        $perPage = new Element\Text('per_page');
        $perPage->setLabel('Số lượng dòng trên một trang mặc định: ')
            ->setAttributes([
                'id' => 'per_page',
                'class' => 'form-control',
                'placeholder'   => 'Số lượng dòng trên một trang mặc định: '
            ]);
        $this->add($perPage);
        
        $perPages = new Element\Text('per_pages');
        $perPages->setLabel('Cấu hình số lượng dòng trên 1 trang: ')
            ->setAttributes([
                'id' => 'per_pages',
                'class' => 'form-control',
                'placeholder'   => 'Cấu hình số lượng dòng trên 1 trang: '
            ]);
        $this->add($perPages);
        
        $pageRange = new Element\Text('page_range');
        $pageRange->setLabel('Số lượng phân trang: ')
            ->setAttributes([
                'id' => 'page_range',
                'class' => 'form-control',
                'placeholder'   => 'Số lượng phân trang: '
            ]);
        $this->add($pageRange);
        
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
                            NotEmpty::IS_EMPTY => "Phải chọn một công ty!"
                        ]
                    ]
                ],]
            ]);
        }
        
        $inputFilter->add([
            'name' => 'per_page',
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
        
        $inputFilter->add([
            'name' => 'per_pages',
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
                        'max' => 256,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'page_range',
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
                        'max' => 2,
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