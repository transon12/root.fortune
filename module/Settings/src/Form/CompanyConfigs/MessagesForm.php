<?php

namespace Settings\Form\CompanyConfigs;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Digits;

class MessagesForm extends Form{
    
    public function __construct($route = '') {
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form-modal',
            'route' => $route,
            'enctype' => 'multipart/form-data',
            'data-target' => '#xlarge'
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){
        
        $messageSuccess = new Element\Text('message_success');
        $messageSuccess->setLabel('Tin nhắn thành công: ')
            ->setAttributes([
                'id' => 'message_success',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn thành công: '
            ]);
        $this->add($messageSuccess);
        
        $messageInvalid = new Element\Text('message_invalid');
        $messageInvalid->setLabel('Tin nhắn sai mã: ')
            ->setAttributes([
                'id' => 'message_invalid',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn sai mã: '
            ]);
        $this->add($messageInvalid);
        
        $messageChecked = new Element\Text('message_checked');
        $messageChecked->setLabel('Tin nhắn đã kiểm tra: ')
            ->setAttributes([
                'id' => 'message_checked',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn đã kiểm tra: '
            ]);
        $this->add($messageChecked);
        
        $messageOutdate = new Element\Text('message_outdate');
        $messageOutdate->setLabel('Tin nhắn hết hạn: ')
            ->setAttributes([
                'id' => 'message_outdate',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn hết hạn: '
            ]);
        $this->add($messageOutdate);
        
        $messageCheckedLimit = new Element\Text('message_checked_limit');
        $messageCheckedLimit->setLabel('Tin nhắn giới hạn đã kiểm tra: ')
            ->setAttributes([
                'id' => 'message_checked_limit',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn đã kiểm tra: '
            ]);
        $this->add($messageCheckedLimit);

        $messageCheckedLimit = new Element\Text('message_checked_same_phone');
        $messageCheckedLimit->setLabel('Tin nhắn đã kiểm tra cùng một số điện thoại: ')
            ->setAttributes([
                'id' => 'message_checked_same_phone',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn đã kiểm tra cùng một số điện thoại: '
            ]);
        $this->add($messageCheckedLimit);

        $messageSuccessQrcode = new Element\Text('message_success_qrcode');
        $messageSuccessQrcode->setLabel('Tin nhắn thành công khi quét qrcode: ')
            ->setAttributes([
                'id' => 'message_success_qrcode',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn thành công khi quét qrcode: '
            ]);
        $this->add($messageSuccessQrcode);

        $messageCheckedQrcode = new Element\Text('message_checked_qrcode');
        $messageCheckedQrcode->setLabel('Tin nhắn đã kiểm tra khi quét qrcode: ')
            ->setAttributes([
                'id' => 'message_checked_qrcode',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn đã kiểm tra khi quét qrcode: '
            ]);
        $this->add($messageCheckedQrcode);
        
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
            'name' => 'message_success',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 160,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'message_invalid',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 160,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'message_checked',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 160,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'message_outdate',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 160,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
        
    }
}