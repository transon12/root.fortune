<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Supplies\Form\Suppliers;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class AddForm extends Form{
    
    private $action;
    
    public function __construct($route = '') {
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form-modal',
            'route' => $route,
            'enctype' => 'multipart/form-data',
            'data-target' => '#defaultSize'
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){
        
        $name = new Element\Text('name');
        $name->setLabel('Tên nhà cung cấp: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Tên nhà cung cấp: '
            ]);
        $this->add($name);
        
        $address = new Element\Text('address');
        $address->setLabel('Địa chỉ: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Địa chỉ: '
            ]);
        $this->add($address);

        $countriesId = new Element\Select('country_id');
        $countriesId->setLabel('Đất nước: ')
        ->setAttributes([
            'id' => 'country_id',
            'class' => 'form-control',
            'placeholder'   => 'Đất nước: '
        ]);
        $this->add($countriesId);

        $citiesId = new Element\Select('city_id');
        $citiesId->setLabel('Tỉnh - thành phố: ')
        ->setAttributes([
            'id' => 'city_id',
            'class' => 'form-control',
            'placeholder'   => 'Tỉnh - thành phố: '
        ]);
        $this->add($citiesId);

        $districtsId = new Element\Select('district_id');
        $districtsId->setLabel('Quận - huyện: ')
        ->setAttributes([
            'id' => 'district_id',
            'class' => 'form-control',
            'placeholder'   => 'Quận - huyện: '
        ]);
        $this->add($districtsId);

        $wardsId = new Element\Select('ward_id');
        $wardsId->setLabel('Phường - xã: ')
        ->setAttributes([
            'id' => 'ward_id',
            'class' => 'form-control',
            'placeholder'   => 'Phường - xã: '
        ]);
        $this->add($wardsId);
        
        $phone = new Element\Text('phone');
        $phone->setLabel('Điện thoại: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Điện thoại: '
            ]);
        $this->add($phone);
        
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
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => "Tên vật tư không được để trống!"
                        ]
                    ]
                ],
                [
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
            'name' => 'address',
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
                            NotEmpty::IS_EMPTY => "Địa chỉ không được để trống!"
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
            'name' => 'phone',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
        ]);
        
        $inputFilter->add([
            'name' => 'ward_id',
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
                            NotEmpty::IS_EMPTY => "Phải chọn một phường (xã)!"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'country_id',
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
                            NotEmpty::IS_EMPTY => "Phải chọn một đất nước!"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'district_id',
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
                            NotEmpty::IS_EMPTY => "Phải chọn một quận (huyện)!"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'city_id',
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
                            NotEmpty::IS_EMPTY => "Phải chọn một tỉnh (thành phố)!"
                        ]
                    ]
                ],
            ]
        ]);
        
    }
}