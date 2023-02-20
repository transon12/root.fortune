<?php

namespace Admin\Form\UserCrms;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Identical;

class AddForm extends Form
{

    private $action;

    public function __construct($action = 'add')
    {
        $this->action = $action;
        parent::__construct();

        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data'
        ]);

        $this->setElements();
        $this->validatorForm();
    }

    private function setElements()
    {

        $companyId = new Element\Select('user_id');
        $companyId->setLabel('Tài khoản fortune: ')
            ->setAttributes([
                'id' => 'user_id',
                'class' => 'form-control'
            ]);
        $this->add($companyId);

        $id = new Element\Text('id');
        $id->setLabel('Tài khoản CRM: ')
            ->setAttributes([
                'id' => 'username',
                'class' => 'form-control',
                'placeholder'   => 'Tài khoản CRM: '
            ]);
        $this->add($id);

        $name = new Element\Text('name');
        $name->setLabel('Tên CRM: ')
            ->setLabelAttributes([
                //                 'class' => 'col-md-12 control-lable'
            ])
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Tên CRM: '
            ]);
        $this->add($name);

        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Lưu'
            ]
        ]);
    }

    private function validatorForm()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
            'name' => 'user_id',
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
                        NotEmpty::IS_EMPTY => "Phải chọn một tài khoản fortune!"
                    ]
                ]
            ]]
        ]);

        $inputFilter->add([
            'name' => 'id',
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
                            NotEmpty::IS_EMPTY => "Tài khoản CRM không được để trống!"
                        ]
                    ]
                ], [
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
                            NotEmpty::IS_EMPTY => "Tên CRM không được để trống!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 1,
                        'max' => 128,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
    }
}
