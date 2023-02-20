<?php
namespace Supplies\Form\ProposalDetails;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class AddForm extends Form{
    
    private $action;
    
    public function __construct($action = 'add') {
        $this->action = $action;
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
        
        $number = new Element\Text('number');
        $number->setLabel('Số lượng: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Số lượng: '
            ]);
        $this->add($number);

        $supplyId = new Element\Select('supply_id');
        $supplyId->setLabel('Vật tư: ')
        ->setAttributes([
            'id' => 'supply_id',
            'class' => 'form-control',
            'placeholder'   => 'Vật tư: '
        ]);
        if($this->action == 'edit'){
            $supplyId->setAttribute('disabled', true);
        }
        $this->add($supplyId);
        
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
            'name' => 'number',
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
                            NotEmpty::IS_EMPTY => "Số lượng không được để trống!"
                        ]
                    ]
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 4,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự",
                        ]
                    ]
                ],
            ]
        ]);

        if($this->action != 'edit'){
            $inputFilter->add([
                'name' => 'supply_id',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags']
                ],
                'validators' => [[
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => "Phải chọn một Vật tư!"
                            ]
                        ]
                    ],
                ]
            ]);
        }
        
    }
}