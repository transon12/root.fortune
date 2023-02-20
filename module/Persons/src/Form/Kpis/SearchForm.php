<?php

namespace Persons\Form\Kpis;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\StringLength;

class SearchForm extends Form{
    public function __construct(){
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

    public function setElements(){
        // $keyword = new Element\Text('keyword');
        // $keyword->setLabel('Từ khóa: ')
        //     ->setAttributes([
        //         'id' => 'keyword',
        //         'class' => 'form-control',
        //         'placeholder' => 'Từ khóa'
        //     ]);
        // $this->add($keyword);

        $year = new Element\Select('year');
        $year->setLabel('KPI năm: ')
            ->setAttributes([
                'id' => 'year',
                'class' => 'form-control',
                'placeholder'   => 'KPI năm: '
            ]);
        $this->add($year);
        
        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Tìm'
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