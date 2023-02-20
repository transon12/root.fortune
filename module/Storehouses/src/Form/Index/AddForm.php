<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Storehouses\Form\Index;

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
        $name->setLabel('Tên kho: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Tên kho: '
            ]);
        $this->add($name);
        
        $address = new Element\Text('address');
        $address->setLabel('Địa chỉ: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Địa chỉ: '
            ]);
        $this->add($address);

        // $countryId = new Element\Select('country_id');
        // $countryId->setLabel('Đất nước: ')
        // ->setAttributes([
        //     'id' => 'country_id',
        //     'class' => 'form-control',
        //     'placeholder'   => 'Đất nước: '
        // ]);
        // $this->add($countryId);

        // $cityId = new Element\Select('city_id');
        // $cityId->setLabel('Tỉnh - thành phố: ')
        // ->setAttributes([
        //     'id' => 'city_id',
        //     'class' => 'form-control',
        //     'placeholder'   => 'Tỉnh - thành phố: '
        // ]);
        // $this->add($cityId);

        // $districtId = new Element\Select('district_id');
        // $districtId->setLabel('Quận - huyện: ')
        // ->setAttributes([
        //     'id' => 'district_id',
        //     'class' => 'form-control',
        //     'placeholder'   => 'Quận - huyện: '
        // ]);
        // $this->add($districtId);

        // $wardId = new Element\Select('ward_id');
        // $wardId->setLabel('Phường - xã: ')
        // ->setAttributes([
        //     'id' => 'ward_id',
        //     'class' => 'form-control',
        //     'placeholder'   => 'Phường - xã: '
        // ]);
        // $this->add($wardId);
        
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
                            NotEmpty::IS_EMPTY => "Tên kho không được để trống!"
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
        
        // $inputFilter->add([
        //     'name' => 'ward_id',
        //     'required' => true,
        //     'filters' => [
        //         ['name' => 'StringTrim'],
        //         ['name' => 'StripTags']
        //     ],
        //     'validators' => [[
        //             'name' => 'NotEmpty',
        //             'break_chain_on_failure' => true,
        //             'options' => [
        //                 'messages' => [
        //                     NotEmpty::IS_EMPTY => "Phải chọn một phường (xã)!"
        //                 ]
        //             ]
        //         ],
        //     ]
        // ]);
        
        // $inputFilter->add([
        //     'name' => 'country_id',
        //     'required' => true,
        //     'filters' => [
        //         ['name' => 'StringTrim'],
        //         ['name' => 'StripTags']
        //     ],
        //     'validators' => [[
        //             'name' => 'NotEmpty',
        //             'break_chain_on_failure' => true,
        //             'options' => [
        //                 'messages' => [
        //                     NotEmpty::IS_EMPTY => "Phải chọn một đất nước!"
        //                 ]
        //             ]
        //         ],
        //     ]
        // ]);
        
        // $inputFilter->add([
        //     'name' => 'district_id',
        //     'required' => true,
        //     'filters' => [
        //         ['name' => 'StringTrim'],
        //         ['name' => 'StripTags']
        //     ],
        //     'validators' => [[
        //             'name' => 'NotEmpty',
        //             'break_chain_on_failure' => true,
        //             'options' => [
        //                 'messages' => [
        //                     NotEmpty::IS_EMPTY => "Phải chọn một quận (huyện)!"
        //                 ]
        //             ]
        //         ],
        //     ]
        // ]);
        
        // $inputFilter->add([
        //     'name' => 'city_id',
        //     'required' => true,
        //     'filters' => [
        //         ['name' => 'StringTrim'],
        //         ['name' => 'StripTags']
        //     ],
        //     'validators' => [[
        //             'name' => 'NotEmpty',
        //             'break_chain_on_failure' => true,
        //             'options' => [
        //                 'messages' => [
        //                     NotEmpty::IS_EMPTY => "Phải chọn một tỉnh (thành phố)!"
        //                 ]
        //             ]
        //         ],
        //     ]
        // ]);
        
    }
}