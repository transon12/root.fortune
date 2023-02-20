<?php
namespace Promotions\Form\Prizes;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Digits;

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
        $name = new Element\Text('name');
        $name->setLabel('Giải thưởng: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Giải thưởng: '
            ]);
        $this->add($name);
        
        $numberWin = new Element\Text('number_win');
        $numberWin->setLabel('Số lượng trúng thưởng: ')
            ->setAttributes([
                'class' => 'form-control',
                'value'   => '1'
            ]);
        $this->add($numberWin);
        
        $numberWon = new Element\Text('number_won');
        $numberWon->setLabel('Số lượng đã trúng: ')
            ->setAttributes([
                'class' => 'form-control',
                'value' => '0',
                'readonly' => true
            ]);
        $this->add($numberWon);
        
        $timeDial = new Element\Text('time_dial');
        $timeDial->setLabel('Thời gian quay số: ')
            ->setAttributes([
                'class' => 'form-control',
                'value' => '0'
            ]);
        $this->add($timeDial);
        
        $priceTopup = new Element\Select('price_topup');
        $priceTopup->setLabel('Số tiền trúng thưởng: ')
            ->setAttributes([
                'class' => 'form-control',
                'value' => '0'
            ])
            ->setValueOptions(\Admin\Service\Digital::returnPriceTopup());
        $this->add($priceTopup);
        
        $message = new Element\Textarea('message');
        $message->setLabel('Tin nhắn trúng thưởng: ')
            ->setAttributes([
                'rows'       => 2,
                'id' => 'description',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn trúng thưởng: '
            ]);
        $this->add($message);
        
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
            'name' => 'name',
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
                            NotEmpty::IS_EMPTY => "Tên không được để trống!"
                        ]
                    ]
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 2,
                        'max' => 255,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'number_win',
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
                            NotEmpty::IS_EMPTY => "Số lượng trúng thưởng không được để trống!"
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
            'name' => 'time_dial',
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