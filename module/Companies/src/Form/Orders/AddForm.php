<?php

namespace Companies\Form\Orders;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Digits;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class AddForm extends Form{

    public function __construct($route = ''){
        parent::__construct();

        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form-modal',
            'route' => $route,
            'enctype' => 'multipart/form-data',
            'data-target' => '#xlarge',
        ]);

        $this->setElements();
        $this->validatorForm();
    }

    private function setElements(){
        
        $code = new Element\Text('code');
        $code->setLabel('Mã hợp đồng: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Mã hợp đồng: '
            ]);
        $this->add($code);

        $companyId = new Element\Select('company_id');
        $companyId->setLabel('Khách hàng: ')
            ->setAttributes([
                'id' => 'company_id',
                'class' => 'select2 form-control',
            ]);
        $this->add($companyId);

        $addressesId = new Element\Select('addresses_id');
        $addressesId->setLabel('Địa chỉ giao hàng: ')
            ->setAttributes([
                'id' => 'addresses_id',
                'class' => 'form-control',
            ]);
        $this->add($addressesId);

        $surrogatesId = new Element\Select('surrogates_id');
        $surrogatesId->setLabel('Người nhận hàng: ')
            ->setAttributes([
                'id' => 'surrogates_id',
                'class' => 'form-control',
            ]);
        $this->add($surrogatesId);

        $numberOrder = new Element\Text('number_order');
        $numberOrder->setLabel('Số lượng đặt: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Số lượng đặt: '
            ]);
        $this->add($numberOrder);

        $deliveryRequest = new Element\Text('delivery_request');
        $deliveryRequest->setLabel('Thời gian giao hàng yêu cầu: ')
            ->setAttributes([
                'id' => 'delivery_request',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Thời gian giao hàng yêu cầu: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($deliveryRequest);

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
            'name' => 'code',
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
                            NotEmpty::IS_EMPTY => "Mã hợp đồng không được để trống!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 2,
                        'max' => 32,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ký tự"
                        ]
                    ]
                ],
            ]
        ]);

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
                            NotEmpty::IS_EMPTY => "Bắt buộc phải chọn khách hàng!"
                        ]
                    ]
                ]
            ]
        ]);

        $inputFilter->add([
            'name' => 'addresses_id',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => []
        ]);
        
        $inputFilter->add([
            'name' => 'surrogates_id',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => []
        ]);

        $inputFilter->add([
            'name' => 'number_order',
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
                            NotEmpty::IS_EMPTY => "Bắt buộc phải có số lượng!"
                        ]
                    ]
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 2,
                        'max' => 128,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ký tự"
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