<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Persons\Form\Leaves;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Identical;
use Zend\Validator\EmailAddress;
use Zend\Validator\Hostname;

class EditForm extends Form{
    
    public function __construct($route = null) {
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form-modal',
            'route' => $route,
            'enctype' => 'multipart/form-data',
            'data-target' => '#defaultSize',
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){

        $totalLeave = new Element\Text("total_leave");
        $totalLeave->setLabel("Số ngày phép: ")
            ->setAttributes([
                'id' => 'total_leave',
                'class' => 'form-control',
                'placeholder' => 'Số ngày phép: '
            ]);
        $this->add($totalLeave);

        $leaveDayUsed = new Element\Text("leave_day_used");
        $leaveDayUsed->setLabel("Số ngày đã sử dụng: ")
            ->setAttributes([
                'id' => 'leave_day_used',
                'class' => 'form-control',
                'placeholder' => 'Số ngày đã sử dụng: '
            ]);
        $this->add($leaveDayUsed);
        
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
            'name' => 'total_leave',
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
                            NotEmpty::IS_EMPTY => "Số ngày phép không được để trống!"
                        ]
                    ]
                ],
            ]
        ]);

        $inputFilter->add([
            'name' => 'leave_day_used',
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
                            NotEmpty::IS_EMPTY => "Số ngày đã sử dụng không được để trống!"
                        ]
                    ]
                ],
            ]
        ]);
    }
}