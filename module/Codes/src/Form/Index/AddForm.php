<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Codes\Form\Index;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Digits;

class AddForm extends Form{
    
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
        $companyId = new Element\Select('company_id');
        $companyId->setLabel('Công ty: ')
            ->setAttributes([
                'id' => 'company_id',
                'class' => 'form-control',
                'placeholder'   => 'Công ty: '
            ]);
        $this->add($companyId);

        $productsId = new Element\Select('products_id');
        $productsId->setLabel('Sản phẩm: ')
        ->setAttributes([
            'id' => 'products_id',
            'class' => 'form-control',
            'placeholder'   => 'Sản phẩm: '
        ]);
        $this->add($productsId);
        
        $numberSerial = new Element\Text('number_serial');
        $numberSerial->setLabel('Số ký tự serial: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Số ký tự serial: ',
                'value' => '7'
            ]);
        $this->add($numberSerial);
        
        $numberCreated = new Element\Text('number_created');
        $numberCreated->setLabel('Số lượng: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Số lượng: '
            ]);
        $this->add($numberCreated);
        
        $isQrcode = new Element\Radio('is_qrcode');
        $isQrcode->setLabel('Có QRCode? ')
            ->setAttributes([
                'value'     => '1',
                'class'     => 'radio-col-brown',
                'style'     => 'margin-left: 10px; margin-right: 3px;'
            ])
            ->setValueOptions([
                'yes_qrcode' => [
                    'label' => 'Có', 
                    'label_attributes' => ['for' => 'yes_qrcode'], 
                    'value' => '1', 
                    'attributes' => ['id' => 'yes_qrcode']
                ],
                'no_qrcode' => [
                    'label' => 'Không', 
                    'label_attributes' => ['for' => 'no_qrcode'], 
                    'value' => '0', 
                    'attributes' => ['id' => 'no_qrcode']
                ]
            ]);
        $this->add($isQrcode);
        
        $status = new Element\Radio('status');
        $status->setLabel('Kích hoạt? ')
            ->setAttributes([
                'value'     => '1',
                'class'     => 'radio-col-brown',
                'style'     => 'margin-left: 10px; margin-right: 3px;'
            ])
            ->setValueOptions([
                'yes' => [
                    'label' => 'Có', 
                    'label_attributes' => ['for' => 'yes'], 
                    'value' => '1', 
                    'attributes' => ['id' => 'yes']
                ],
                'no' => [
                    'label' => 'Không', 
                    'label_attributes' => ['for' => 'no'], 
                    'value' => '0', 
                    'attributes' => ['id' => 'no']
                ]
            ]);
        $this->add($status);
        
        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Tạo mã'
            ]
        ]);
    }
    
    private function validatorForm(){
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
            'name' => 'company_id',
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
                        NotEmpty::IS_EMPTY => "Phải chọn một công ty!"
                    ]
                ]
            ],]
        ]);

        $inputFilter->add([
            'name' => 'products_id',
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
                        NotEmpty::IS_EMPTY => "Phải chọn một sản phẩm!"
                    ]
                ]
            ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'number_serial',
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
                            NotEmpty::IS_EMPTY => "Số ký tự serial không được để trống!"
                        ]
                    ]
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 2,
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
            'name' => 'number_created',
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
                            NotEmpty::IS_EMPTY => "Số ký tự serial không được để trống!"
                        ]
                    ]
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 7,
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
        
    }
}