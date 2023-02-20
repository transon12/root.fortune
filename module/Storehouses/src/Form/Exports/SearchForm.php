<?php

namespace Storehouses\Form\Exports;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\StringLength;

class SearchForm extends Form{
    
    public function __construct() {
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
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Thời gian bắt đầu: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($datetimeBegin);
        
        $datetimeEnd = new Element\Text('datetime_end');
        $datetimeEnd->setLabel('Thời gian kết thúc: ')
            ->setAttributes([
                'id' => 'datetime_end',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Thời gian kết thúc: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($datetimeEnd);
        
        $this->add([
            'name'  => 'btnSearch',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Tìm kiếm'
            ]
        ]);
        
        $this->add([
            'name'  => 'btnExport',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Xuất excel'
            ]
        ]);
    }
    
    private function validatorForm(){
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        
    }
}