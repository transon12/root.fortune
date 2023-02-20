<?php

namespace Storehouses\Form\Details;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\Digits;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class AddForm extends Form{
    
    private $settingDatas;
    private $userId;
    
    public function __construct($userId = null) {
        $this->userId = $userId;
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            // 'route' => $route,
            'enctype' => 'multipart/form-data',
            'method' => 'POST'
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){ 

        $productsId = new Element\Select('products_id');
        $productsId->setLabel('Sản phẩm: ')
            ->setAttributes([
                'class' => 'select2 form-control',
                'placeholder'   => 'Sản phẩm: '
            ]);
        $this->add($productsId);

        $codes = new Element\Text('codes');
        $codes->setLabel('Số serial hoặc qrcode: ')
            ->setAttributes([
                'id' => 'codes',
                'class' => 'form-control',
                'placeholder'   => 'Số serial hoặc qrcode'
            ]);
        $this->add($codes);

        $exportedAt = new Element\Text('exported_at');
        $exportedAt->setLabel('Thời gian xuất: ')
            ->setAttributes([
                'id' => 'exported_at',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Thời gian xuất: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($exportedAt);

        $unitPrice = new Element\Text('unit_price');
        $unitPrice->setLabel('Đơn giá: ')
                ->setAttributes([
                    'class' => 'form-control',
                    'placeholder' => 'Nhập đơn giá'
                ]);
        $this->add($unitPrice);

        $quantity = new Element\Text('quantity');
        $quantity->setLabel('Số lượng: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder' => 'Nhập'
            ]);
        $this->add($quantity);
        
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
            'name' => 'codes',
            'required' => true,
            'filters' => [
                ['name'=>'StringTrim'],
                ['name'=>'StripTags']
            ],
            'validators' => [[
                'name' => 'NotEmpty',
                'break_chain_on_failure' => true,
                'options' => [
                    'messages' => [
                        NotEmpty::IS_EMPTY => "Không được để trống!"
                    ]
                ]
                ],
            ]
        ]);

        // $inputFilter->add([
        //     'name' => 'quantity',
        //     'required' => true,
        //     'filters' => [
        //         ['name'=>'StringTrim']
        //     ],
        //     'validators' => [
        //         [
        //             'name'=>'NotEmpty',
        //             'break_chain_on_failure'=>true,
        //             'options'=>[
        //                 'messages'=>[
        //                     NotEmpty::IS_EMPTY => "Nhập số lượng"
        //                 ]
        //             ]
        //         ],
        //         [
        //             'name' =>'Digits',
        //             'break_chain_on_failure' => true,
        //             'options'=>[
        //                 'messages'=>[
        //                     Digits::NOT_DIGITS => 'Hãy nhập số'
        //                 ]
        //             ]
        //         ],
        //     ]
        // ]);

        // $inputFilter->add([
        //     'name' => 'unit_price',
        //     'required' => true,
        //     'filters' => [
        //         ['name'=>'StringTrim']
        //     ],
        //     'validators' => [
        //         [
        //             'name'=>'NotEmpty',
        //             'break_chain_on_failure'=>true,
        //             'options'=>[
        //                 'messages'=>[
        //                     NotEmpty::IS_EMPTY => "Nhập đơn giá"
        //                 ]
        //             ]
        //         ],
        //         [
        //             'name' =>'Digits',
        //             'break_chain_on_failure' => true,
        //             'options'=>[
        //                 'messages'=>[
        //                     Digits::NOT_DIGITS => 'Hãy nhập số'
        //                 ]
        //             ]
        //         ],
        //     ]
        // ]);

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
            
    }
}