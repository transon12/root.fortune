<?php

namespace Settings\Form\CompanyConfigs;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Digits;

class SuppliesForm extends Form{
    
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
        
        $timeLimitSuppliesIns = new Element\Text('time_limit_supplies_ins');
        $timeLimitSuppliesIns->setLabel('Thời gian tối đa cho phép sửa, xoá vật tư sau khi nhập kho: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Thời gian tối đa cho phép sửa, xoá vật tư sau khi nhập kho: '
            ]);
        $this->add($timeLimitSuppliesIns);
        
        $timeLimitSuppliesOuts = new Element\Text('time_limit_supplies_outs');
        $timeLimitSuppliesOuts->setLabel('Thời gian tối đa cho phép sửa, xoá vật tư sau khi xuất kho: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Thời gian tối đa cho phép sửa, xoá vật tư sau khi xuất kho: '
            ]);
        $this->add($timeLimitSuppliesOuts);
        
        $timeLimitProposalDetails = new Element\Text('time_limit_proposal_details');
        $timeLimitProposalDetails->setLabel('Thời gian tối đa cho phép thêm, sửa, xoá chi tiết đề xuất: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Thời gian tối đa cho phép thêm, sửa, xoá chi tiết đề xuất: '
            ]);
        $this->add($timeLimitProposalDetails);
        
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
            'name' => 'time_limit_supplies_ins',
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
                        'max' => 8,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'time_limit_supplies_outs',
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
                        'max' => 8,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'time_limit_proposal_details',
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
                        'max' => 8,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
        
    }
}