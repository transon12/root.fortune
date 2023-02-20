<?php
namespace Statistics\Form\Warranties;

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
        $title = new Element\Text('title');
        $title->setLabel('Tiêu đề: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Tiêu đề: '
            ]);
        $this->add($title);
        
        $content = new Element\Textarea('content');
        $content->setLabel('Nội dung bảo hành: ')
            ->setAttributes([
                'class' => 'form-control',
            ]);
        $this->add($content);
        
        $price = new Element\Text('price');
        $price->setLabel('Chi phí (nếu có): ')
            ->setAttributes([
                'id' => 'price',
                'class' => 'form-control pxt-currency',
                'placeholder'   => '0',
                'aria-describedby' => 'basic-addon2'
            ]);
        $this->add($price);
        
        $datetimeReceive = new Element\Text('datetime_receive');
        $datetimeReceive->setLabel('Thời gian nhận bảo hành: ')
            ->setAttributes([
                'id' => 'datetime_receive',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Thời gian nhận bảo hành: ',
                'data-toggle' => 'datetimepicker',
                //'data-target' => '#datetime_receive'
            ]);
        $this->add($datetimeReceive);
        
        $datetimeReturn = new Element\Text('datetime_return');
        $datetimeReturn->setLabel('Thời gian trả bảo hành: ')
            ->setAttributes([
                'id' => 'datetime_return',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Thời gian trả bảo hành: ',
                'data-toggle' => 'datetimepicker',
                //'data-target' => '#datetime_return'
            ]);
        $this->add($datetimeReturn);
        
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
            'name' => 'title',
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
                            NotEmpty::IS_EMPTY => "Tiêu đề không được để trống!"
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
            'name' => 'datetime_receive',
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
                            NotEmpty::IS_EMPTY => "Thời gian nhận bảo hành không được để trống!"
                        ]
                    ]
                ]
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'datetime_return',
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
                            NotEmpty::IS_EMPTY => "Thời gian trả bảo hành không được để trống!"
                        ]
                    ]
                ]
            ]
        ]);
        
    }
}