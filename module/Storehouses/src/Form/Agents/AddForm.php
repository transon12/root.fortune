<?php

namespace Storehouses\Form\Agents;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class AddForm extends Form{
    
    private $settingDatas;
    private $userId;
    
    public function __construct($route = '', $settingDatas = null, $userId = null) {
        $this->settingDatas = $settingDatas;
        $this->userId = $userId;
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
        $name = new Element\Text('name');
        $name->setLabel('Tên đại lý: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Tên đại lý: '
            ]);
        $this->add($name);
        
        $code = new Element\Text('code');
        $code->setLabel('Mã đại lý: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Mã đại lý: '
            ]);
        $this->add($code);
            
        if(!empty($this->settingDatas)){
            foreach($this->settingDatas as $key => $item){
                if($item['type'] == "Textarea"){
                    $data = new Element\Textarea($key);
                    $data->setLabel($item['name'])
                    ->setAttributes([
                        'rows'          => 2,
                        'id'            => $key,
                        'class'         => 'form-control',
                        'placeholder'   => $item['name']
                    ]);
                    $this->add($data);
                }elseif($item['type'] == "File" || $item['type'] == "Image"){
                    $data = new Element\File($key);
                    $data->setLabel($item['name'])
                    ->setAttributes([
                        'id'    => $key
                    ]);
                    $this->add($data);
                }else{
                    $data = new Element\Text($key);
                    $data->setLabel($item['name'])
                        ->setAttributes([
                            'class'         => 'form-control',
                            'placeholder'   => $item['name']
                        ]);
                    $this->add($data);
                }
            }
        }
        
        $address = new Element\Text('address');
        $address->setLabel('Địa chỉ: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Địa chỉ: '
            ]);
        $this->add($address);

        // $countriesId = new Element\Select('country_id');
        // $countriesId->setLabel('Đất nước: ')
        // ->setAttributes([
        //     'id' => 'country_id',
        //     'class' => 'form-control',
        //     'placeholder'   => 'Đất nước: '
        // ]);
        // $this->add($countriesId);

        // $citiesId = new Element\Select('city_id');
        // $citiesId->setLabel('Tỉnh - thành phố: ')
        // ->setAttributes([
        //     'id' => 'city_id',
        //     'class' => 'form-control',
        //     'placeholder'   => 'Tỉnh - thành phố: '
        // ]);
        // $this->add($citiesId);

        // $districtsId = new Element\Select('district_id');
        // $districtsId->setLabel('Quận - huyện: ')
        // ->setAttributes([
        //     'id' => 'district_id',
        //     'class' => 'form-control',
        //     'placeholder'   => 'Quận - huyện: '
        // ]);
        // $this->add($districtsId);

        // $wardsId = new Element\Select('ward_id');
        // $wardsId->setLabel('Phường - xã: ')
        // ->setAttributes([
        //     'id' => 'ward_id',
        //     'class' => 'form-control',
        //     'placeholder'   => 'Phường - xã: '
        // ]);
        // $this->add($wardsId);
        
        $description = new Element\Textarea('description');
        $description->setLabel('Mô tả thông tin: ')
            ->setAttributes([
                'rows'       => 5,
                'id' => 'description',
                'class' => 'form-control',
                'placeholder'   => 'Mô tả thông tin: '
            ]);
        $this->add($description);
        
        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Lưu'
            ]
        ]);
        
        $status = new Element\Radio('status');
        $status->setLabel('Trạng thái: ')
            ->setAttributes([
                'value'     => '1',
                'class'     => 'radio-col-brown',
                'style'     => 'margin-left: 10px; margin-right: 3px;'
            ])
            ->setValueOptions([
                'yes' => [
                    'label' => 'Hoạt động', 
                    'label_attributes' => ['for' => 'yes'], 
                    'value' => '1', 
                    'attributes' => ['id' => 'yes']
                ],'no' => [
                    'label' => 'Không hoạt động', 
                    'label_attributes' => ['for' => 'no'], 
                    'value' => '0', 
                    'attributes' => ['id' => 'no']
                ]
            ]);
        $this->add($status);
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
                            NotEmpty::IS_EMPTY => "Không được để trống!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 2,
                        'max' => 128,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui long nhap duoi %max% ky tu",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ky tu"
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
            'validators' => [[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 16,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui long nhap duoi %max% ky tu"
                        ]
                    ]
                ],
            ]
        ]);
        
        // $inputFilter->add([
        //     'name' => 'address',
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
        //                     NotEmpty::IS_EMPTY => "Địa chỉ không được để trống!"
        //                 ]
        //             ]
        //         ],[
        //             'name' => 'StringLength',
        //             'break_chain_on_failure' => true,
        //             'options' => [
        //                 'min' => 2,
        //                 'max' => 255,
        //                 'messages' => [
        //                     StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự",
        //                     StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ký tự"
        //                 ]
        //             ]
        //         ],
        //     ]
        // ]);
        
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