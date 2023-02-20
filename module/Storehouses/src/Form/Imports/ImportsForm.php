<?php

namespace Storehouses\Form\Imports;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;

class ImportsForm extends Form{
    
    private $settingDatas;
    
    public function __construct($settingDatas = []) {
        $this->settingDatas = $settingDatas;
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data',
            'method' => 'POST'
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){

        $status = new Element\Select('status');
        $status->setLabel('Trạng thái: ')
        ->setAttributes([
            'id' => 'status',
            'class' => 'form-control'
        ])
        ->setValueOptions(["1" => "Nhập kho", "0" => "Reset mã"]);
        $this->add($status);

        $codes = new Element\Text('codes');
        $codes->setLabel('Số serial hoặc qrcode: ')
            ->setAttributes([
                'id' => 'codes',
                'class' => 'form-control',
                'placeholder'   => 'Số serial hoặc qrcode'
            ]);
        $this->add($codes);
        
        $productsId = new Element\Select('products_id');
        $productsId->setLabel('Sản phẩm: ')
            ->setAttributes([
                'class' => 'select2 form-control',
                'placeholder'   => 'Sản phẩm: '
            ]);
        $this->add($productsId);
        
        $datetimeImport = new Element\Text('datetime_import');
        $datetimeImport->setLabel('Thời gian nhập kho: ')
            ->setAttributes([
                'id' => 'datetime_import',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Thời gian nhập kho: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($datetimeImport);
            
        if(!empty($this->settingDatas)){
            foreach($this->settingDatas as $key => $item){
                // die('abc');
                $data = new Element\Text($key);
                $data->setLabel($item['name'])
                    ->setAttributes([
                        'class'         => 'form-control',
                        'placeholder'   => $item['name']
                    ]);
                $this->add($data);
            }
        }
        
        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Thực thi'
            ]
        ]);
        
        $this->add([
            'name'  => 'btnExport',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Xuất excel'
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
                ],
            ]
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
            'name' => 'datetime_import',
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
                ],
            ]
        ]);
        
    }
}