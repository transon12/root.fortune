<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Promotions\Form\Offers;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Digits;

class AddForm extends Form{
    
    private $action;
    
    public function __construct($route = '') {
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form-modal',
            'route' => $route,
            'enctype' => 'multipart/form-data',
            'data-target' => '#large'
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){
        
        $staff = new Element\Text('staff');
        $staff->setLabel('Nhân viên đề xuất: ')
            ->setAttributes([
                'class'         => 'form-control',
                'placeholder'   => 'Nhân viên đề xuất: '
            ]);
        $this->add($staff);

        $request = new Element\Select('request');
        $request->setLabel('Yêu cầu: ')
        ->setAttributes([
            'id'            => 'request',
            'class'         => 'form-control',
            'value'         => '0'
        ])
        ->setValueOptions(\Promotions\Model\Offers::returnRequest());
        $this->add($request);
        
        $requestedAt = new Element\Text('requested_at');
        $requestedAt->setLabel('Ngày yêu cầu: ')
            ->setAttributes([
                'id'            => 'requested_at',
                'class'         => 'form-control datetimepicker-input',
                'placeholder'   => 'Ngày yêu cầu: ',
                'data-toggle'   => 'datetimepicker',
            ]);
        $this->add($requestedAt);

        $productId = new Element\Select('product_id');
        $productId->setLabel('Sản phẩm đề xuất: ')
        ->setAttributes([
            'id'            => 'product_id',
            'class'         => 'form-control',
            'value'         => '0'
        ]);
        $this->add($productId);
        
        $content = new Element\Textarea('content');
        $content->setLabel('Nội dung đề xuất: ')
            ->setAttributes([
                'rows'          => 6,
                'id'            => 'content',
                'class'         => 'form-control',
                'placeholder'   => 'Nội dung đề xuất: '
            ]);
        $this->add($content);
        
        $phone = new Element\Text('phone');
        $phone->setLabel('Số điện thoại khách hàng: ')
            ->setAttributes([
                'class'         => 'form-control',
                'placeholder'   => 'Số điện thoại khách hàng: '
            ]);
        $this->add($phone);
        
        $info = new Element\Textarea('info');
        $info->setLabel('Thông tin khách hàng: ')
            ->setAttributes([
                'rows'          => 4,
                'id'            => 'info',
                'class'         => 'form-control',
                'placeholder'   => 'Thông tin khách hàng: '
            ]);
        $this->add($info);

        $reponse = new Element\Select('reponse');
        $reponse->setLabel('Kết quả xử lý: ')
        ->setAttributes([
            'id'            => 'reponse',
            'class'         => 'form-control',
            'value'         => '0'
        ])
        ->setValueOptions(\Promotions\Model\Offers::returnResponse());
        $this->add($reponse);
        
        $code = new Element\Text('code');
        $code->setLabel('Mã đơn trả thưởng: ')
            ->setAttributes([
                'class'         => 'form-control',
                'placeholder'   => 'Mã đơn trả thưởng: '
            ]);
        $this->add($code);
        
        $reponsedAt = new Element\Text('reponsed_at');
        $reponsedAt->setLabel('Ngày trả kết quả: ')
            ->setAttributes([
                'id'            => 'reponsed_at',
                'class'         => 'form-control datetimepicker-input',
                'placeholder'   => 'Ngày trả: ',
                'data-toggle'   => 'datetimepicker',
            ]);
        $this->add($reponsedAt);
        
        // $address = new Element\Text('address');
        // $address->setLabel('Địa chỉ: ')
        //     ->setAttributes([
        //         'class' => 'form-control',
        //         'placeholder'   => 'Địa chỉ: '
        //     ]);
        // $this->add($address);

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
            'name' => 'staff',
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
                            NotEmpty::IS_EMPTY => "'Nhân viên đề xuất' không được để trống!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 2,
                        'max' => 100,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'content',
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
                            NotEmpty::IS_EMPTY => "'Nội dung' không được để trống!"
                        ]
                    ]
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 1,
                        'max' => 1000,
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
                            NotEmpty::IS_EMPTY => "'Số điện thoại' không được để trống!"
                        ]
                    ]
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 10,
                        'max' => 11,
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
        
        $inputFilter->add([
            'name' => 'info',
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
                            NotEmpty::IS_EMPTY => "'Thông tin' không được để trống!"
                        ]
                    ]
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 1,
                        'max' => 1000,
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