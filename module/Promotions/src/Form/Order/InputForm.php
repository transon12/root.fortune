<?php

namespace Promotions\Form\Order;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Digits;

class InputForm extends Form
{

    private $action;

    public function __construct($route = '')
    {
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

    private function setElements()
    {
        $phoneRecipient = new Element\Text('phone_recipient');
        $phoneRecipient->setLabel('Số điện thoại người nhận: ')
            ->setAttributes([
                'id' => 'phone_recipient',
                'class' => 'form-control',
                'placeholder'   => 'Số điện thoại người nhận: '
            ]);
        $this->add($phoneRecipient);

        $fullnameRecipient = new Element\Text('fullname_recipient');
        $fullnameRecipient->setLabel('Tên người nhận: ')
            ->setAttributes([
                'id' => 'fullname_recipient',
                'class' => 'form-control',
                'placeholder'   => 'Tên người nhận: '
            ]);
        $this->add($fullnameRecipient);

        $addressRecipient = new Element\Text('address_recipient');
        $addressRecipient->setLabel('Địa chỉ người nhận: ')
            ->setAttributes([
                'id' => 'address_recipient',
                'class' => 'form-control',
                'placeholder'   => 'Địa chỉ người nhận: '
            ]);
        $this->add($addressRecipient);

        $productId = new Element\Select('product_id');
        $productId->setLabel('Sản phẩm được tặng: ')
            ->setAttributes([
                'id' => 'product_id',
                'class' => 'form-control'
            ]);
        $this->add($productId);

        $isFinish = new Element\Checkbox('is_finish');
        $isFinish->setLabel('Xác nhận đã nhập xong: ')
            ->setAttributes([
                'id' => 'is_finish'
            ]);
        $this->add($isFinish);

        $note2 = new Element\Textarea('note_2');
        $note2->setLabel('Ghi chú nhập thông tin1: ')
            ->setAttributes([
                'id'        => 'note_2',
                'class'     => 'form-control',
                'rows'      => '5'
            ]);
        $this->add($note2);

        $plusScoreId = new Element\Select('plusScoreId');
        $plusScoreId->setLabel('Trừ điểm thưởng')
            ->setAttributes([
                'class' => 'form-control',
                'id' => 'plusScoreId',
                'rows'      => '12'
            ]);
        $this->add($plusScoreId);

        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Cập nhật'
            ]
        ]);
    }

    private function validatorForm()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
            'name' => 'phone_recipient',
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
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 10,
                        'max' => 11,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui long nhap duoi %max% ky tu",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ky tu"
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
                ]
            ]
        ]);

        $inputFilter->add([
            'name' => 'fullname_recipient',
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
                            NotEmpty::IS_EMPTY => "'Tên người nhận' không được để trống!"
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
            'name' => 'address_recipient',
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
                            NotEmpty::IS_EMPTY => "'Địa chỉ' không được để trống!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 2,
                        'max' => 400,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui long nhap duoi %max% ky tu",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ky tu"
                        ]
                    ]
                ],
            ]
        ]);

        $inputFilter->add([
            'name' => 'product_id',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [
                // [
                //     'name' => 'NotEmpty',
                //     'break_chain_on_failure' => true,
                //     'options' => [
                //         'messages' => [
                //             NotEmpty::IS_EMPTY => "Phải chọn một sản phẩm!"
                //         ]
                //     ]
                // ],
            ]
        ]);
    }
}
