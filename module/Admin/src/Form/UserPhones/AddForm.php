<?php
namespace Admin\Form\UserPhones;

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
        
        $phone = new Element\Text('phone');
        $phone->setLabel('Số điện thoại: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Số điện thoại: '
            ]);
        $this->add($phone);
        
        $status = new Element\Radio('status');
        $status->setLabel('Trạng thái: ')
            ->setAttributes([
                'value'     => '1',
                'class'     => 'radio-col-brown',
                'style'     => 'margin-left: 10px; margin-right: 3px;'
            ])
            ->setValueOptions(
                [
                'status_1' => [
                    'label' => 'Hoạt động', 
                    'label_attributes' => ['for' => 'status_1'], 
                    'value' => '1', 
                    'attributes' => ['id' => 'status_1']
                ],
                'status_0' => [
                    'label' => 'Không hoạt động', 
                    'label_attributes' => ['for' => 'status_0'], 
                    'value' => '0', 
                    'attributes' => ['id' => 'status_0']
                ]
            ]);
        $this->add($status);
        
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
            'name' => 'phone',
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
                            NotEmpty::IS_EMPTY => "Số điện thoại không được để trống!"
                        ]
                    ]
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 10,
                        'max' => 16,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
        
    }
}