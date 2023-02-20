<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Settings\Form\Index;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Digits;

class ConfigForm extends Form{
    
    public function __construct() {
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data'
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){
        $cookieLifetime = new Element\Text('cookie_lifetime');
        $cookieLifetime->setLabel('Thời gian lưu session: ')
            ->setLabelAttributes([
//                 'class' => 'col-md-12 form-group'
            ])
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Thời gian lưu session: '
            ]);
        $this->add($cookieLifetime);
        
        $rememberMe = new Element\Text('remember_me');
        $rememberMe->setLabel('Thời gian lưu cookie: ')
            ->setLabelAttributes([
//                 'class' => 'col-md-12 form-group'
            ])
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Thời gian lưu cookie: '
            ]);
        $this->add($rememberMe);
        
        $allowIpsConnectApiSms = new Element\Text('allow_ips_connect_api_sms');
        $allowIpsConnectApiSms->setLabel('Danh sách IP được phép kết nối đến API (kết nối tin nhắn): ')
            ->setLabelAttributes([
//                 'class' => 'col-md-12 form-group'
            ])
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Danh sách IP được phép kết nối đến API (kết nối tin nhắn): '
            ]);
        $this->add($allowIpsConnectApiSms);
        
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
            'name' => 'cookie_lifetime',
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
                            NotEmpty::IS_EMPTY => "Thời gian lưu session không được để trống!"
                        ]
                    ]
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 8,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
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
            'name' => 'remember_me',
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
                            NotEmpty::IS_EMPTY => "Thời gian lưu cookie không được để trống!"
                        ]
                    ]
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 8,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
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
            'name' => 'allow_ips_connect_api_sms',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
        ]);
    }
}