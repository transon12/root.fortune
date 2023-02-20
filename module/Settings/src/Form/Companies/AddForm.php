<?php

namespace Settings\Form\Companies;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class AddForm extends Form{
    private $userId;
    
    public function __construct($route = '', $userId = null) {
        parent::__construct();
        $this->userId = $userId;
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

        if($this->userId == 1){
            $idUser = new Element\Select('user_id');
            $idUser->setLabel('Khách hàng của tài khoản: ')
                ->setAttributes([
                    'id' => 'user_id',
                    'class' => 'select2 form-control',
                ]);
            $this->add($idUser);
        }
        
        $id = new Element\Text('id');
        $id->setLabel('Mã công ty: ')
            ->setAttributes([
                'id' => 'id',
                'class' => 'form-control',
                'placeholder'   => 'Mã công ty: '
            ]);
        $this->add($id);
        
        $name = new Element\Text('name');
        $name->setLabel('Tên công ty: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Tên công ty: '
            ]);
        $this->add($name);

        $taxCode = new Element\Text('tax_code');
        $taxCode->setLabel('Mã số thuế: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Mã số thuế: '
            ]);
        $this->add($taxCode);

        $address = new Element\Text('address');
        $address->setLabel('Địa chỉ: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Địa chỉ: '
            ]);
        $this->add($address);
        
        $phone = new Element\Text('phone');
        $phone->setLabel('Điện thoại liên hệ tại địa chỉ này: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Điện thoại liên hệ tại địa chỉ này: '
            ]);
        $this->add($phone);

        $cityId = new Element\Select('city_id');
        $cityId->setLabel('Tỉnh - thành phố: ')
        ->setAttributes([
            'id' => 'city_id',
            'class' => 'form-control',
            'placeholder'   => 'Tỉnh - thành phố: '
        ]);
        $this->add($cityId);

        $districtId = new Element\Select('district_id');
        $districtId->setLabel('Quận - huyện: ')
        ->setAttributes([
            'id' => 'district_id',
            'class' => 'form-control',
            'placeholder'   => 'Quận - huyện: '
        ]);
        $this->add($districtId);

        $wardId = new Element\Select('ward_id');
        $wardId->setLabel('Phường - xã: ')
        ->setAttributes([
            'id' => 'ward_id',
            'class' => 'form-control',
            'placeholder'   => 'Phường - xã: '
        ]);
        $this->add($wardId);
        
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
            'name' => 'id',
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
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 1,
                        'max' => 16,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui long nhap duoi %max% ky tu",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ky tu"
                        ]
                    ]
                ],
            ]
        ]);
        
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
            'name' => 'ward_id',
            'required' => false,
        ]);
        
        $inputFilter->add([
            'name' => 'district_id',
            'required' => false,
        ]);
        
        $inputFilter->add([
            'name' => 'city_id',
            'required' => false,
        ]);

        $inputFilter->add([
            'name' => 'user_id',
            'required' => false,
        ]);
        
    }
}