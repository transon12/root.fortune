<?php

namespace Persons\Form\Kpis;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\Digits;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Identical;
use Zend\Validator\EmailAddress;

class EditForm extends Form{
    public function __construct($route = '') {
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form-modal',
            'route' => $route,
            'enctype' => 'multipart/form-data',
            'data-target' => '#xlarge',
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }

    private function setElements(){
        $year = new Element\Text('year');
        $year->setLabel('KPI năm: ')
            ->setAttributes([
                'id' => 'get_year',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'KPI năm: ',
                'data-toggle' => 'datetimepicker',
                'autocomplete' => 'off',
            ]);
        $this->add($year);

        $target = new Element\Text('target');
        $target->setLabel('Mục tiêu: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Mục tiêu: '
            ]);
        $this->add($target);

        $measure = new Element\Textarea('measure');
        $measure->setLabel('Đo lường: ')
            ->setAttributes([
                'wrap' => 'hard',
                'class' => 'form-control',
                'placeholder'   => 'Đo lường: '
            ]);
        $this->add($measure);

        $expected_results = new Element\Textarea('expected_results');
        $expected_results->setLabel('Kết quả kì vọng: ')
            ->setAttributes([
                'wrap' => 'hard',
                'class' => 'form-control',
                'placeholder'   => 'Kết quả kì vọng: '
            ]);
        $this->add($expected_results);

        $action_program = new Element\Textarea('action_program');
        $action_program->setLabel('Chương trình hành động: ')
            ->setAttributes([
                'wrap' => 'hard',
                'class' => 'form-control',
                'placeholder'   => 'Chương trình hành động: '
            ]);
        $this->add($action_program);

        $results = new Element\Textarea('results');
        $results->setLabel('Kết quả thực hiện: ')
            ->setAttributes([
                'wrap' => 'hard',
                'class' => 'form-control',
                'placeholder'   => 'Kết quả thực hiện: '
            ]);
        $this->add($results);

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
            'name' => 'year',
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
                            NotEmpty::IS_EMPTY => "Hãy chọn năm!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 4,
                        'max' => 4,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ký tự"
                        ]
                    ]
                ],
                [
                    'name' => 'Digits',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'messages' => [
                            Digits::NOT_DIGITS => "Vui lòng nhập năm là số !"
                        ]
                    ]
                ]
            ]
        ]);
    }
}