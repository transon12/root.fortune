<?php

namespace Companies\Form\MissionDetails;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class AddForm extends Form{

    public function __construct($route=''){
        parent::__construct();

        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data',
            
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }

    private function setElements(){

        $missionId = new Element\Select('mission_id');
        $missionId->setLabel('Nhiệm vụ: ')
                ->setAttributes([
                    'id' => 'mission_id',
                    'class' => 'form-control',
                    'placeholder' => 'Nhập nhiệm vụ'
                ]);
        $this->add($missionId);

        $user = new Element\Select('user');
        $user->setLabel('Danh sách tài khoản tham gia: ')
            ->setAttributes([
                'id' => 'user',
                'class' => 'select2 form-control',
                'multiple' => 'multiple',
                'placeholder' => 'Chọn tài khoản'
            ]);
        $this->add($user);

        $beginedAt = new Element\Text('begined_at');
        $beginedAt->setLabel('Thời gian bắt đầu: ')
                ->setAttributes([
                    'id' => 'begined_at',
                    'class' => 'form-control datetimepicker-input',
                    'placeholder'   => 'Thời gian bắt đầu ',
                    'data-toggle' => 'datetimepicker',
                ]);
        $this->add($beginedAt);

        $expectedAt = new Element\Text('expected_at');
        $expectedAt->setLabel('Thời gian dự kiến hoàn thành: ')
                ->setAttributes([
                    'id' => 'expected_at',
                    'class' => 'form-control datetimepicker-input',
                    'placeholder'   => 'Thời gian dự kiến hoàn thành',
                    'data-toggle' => 'datetimepicker',
                ]);
        $this->add($expectedAt);

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

        // $inputFilter->add([
        //     'name' => 'mission_id',
        //     'required' => true,
        //     'filters' => [
        //         ['name' => 'StringTrim'],
        //         ['name' => 'StripTags']
        //     ],
        //     'validators' => [
        //         [
        //             'name' => 'NotEmpty',
        //             'break_chain_on_failure' => true,
        //             'options' => [
        //                 'messages' => [
        //                     NotEmpty::IS_EMPTY => "Nhiệm vụ không được để trống!"
        //                 ]
        //             ]
        //         ],
        //     ]
        // ]);

        $inputFilter->add([
            'name' => 'user',
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
                            NotEmpty::IS_EMPTY => "Phải chọn tài khoản tham gia!"
                        ]
                    ]
                ],
            ]
        ]);
    }
}