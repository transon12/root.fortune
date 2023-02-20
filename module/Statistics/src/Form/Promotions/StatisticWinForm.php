<?php

namespace Statistics\Form\Promotions;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\StringLength;

class StatisticWinForm extends Form{
    
    private $action;
    
    public function __construct($action = 'index') {
        $this->action = $action;
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data',
            'method' => 'GET'
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){
        
        $datetimeBegin = new Element\Text('datetime_begin');
        $datetimeBegin->setLabel('Thời gian bắt đầu: ')
            ->setAttributes([
                'id' => 'datetime_begin',
                'class' => 'form-control',
                'placeholder'   => 'Thời gian bắt đầu: '
            ]);
        $this->add($datetimeBegin);
        
        $datetimeEnd = new Element\Text('datetime_end');
        $datetimeEnd->setLabel('Thời gian kết thúc: ')
            ->setAttributes([
                'id' => 'datetime_end',
                'class' => 'form-control',
                'placeholder'   => 'Thời gian kết thúc: '
            ]);
        $this->add($datetimeEnd);
        
        $keyword = new Element\Text('keyword');
        $keyword->setLabel('Từ khóa: ')
            ->setAttributes([
                'id' => 'keyword',
                'class' => 'form-control',
                'placeholder'   => 'Từ khóa'
            ]);
        $this->add($keyword);
        
        $promotionsId = new Element\Select('promotions_id');
        $promotionsId->setLabel('Chương trình khuyến mãi: ')
            ->setAttributes([
                'class' => 'form-control'
            ]);
        $this->add($promotionsId);
        
        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Tìm'
            ]
        ]);
        
        $this->add([
            'name'  => 'btnExport',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-secondary',
                'value' => 'Xuất excel'
            ]
        ]);
    }
    
    private function validatorForm(){
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        
        $inputFilter->add([
            'name' => 'keyword',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 128,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui long nhap duoi %max% ky tu"
                        ]
                    ]
                ],
            ]
        ]);
        
    }
}