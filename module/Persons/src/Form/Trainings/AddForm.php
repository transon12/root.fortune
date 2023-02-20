<?php

namespace Persons\Form\Trainings;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Digits;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class AddForm extends Form{

    public function __construct($route = null){
        parent::__construct();

        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            'route' => $route,
            'enctype' => 'multipart/form-data',
            // 'data-target' => '#xlarge',
        ]);

        $this->setElements();
        $this->validatorForm();
    }

    private function setElements(){
        
        $name = new Element\Text('name');
        $name->setLabel('Tên khóa đào tạo: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Tên khóa đào tạo: '
            ]);
        $this->add($name);

        $date = new Element\Text('date');
        $date->setLabel('Ngày tham gia: ')
            ->setAttributes([
                'id' => 'date',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Ngày tham gia: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($date);

        $location = new Element\Text('location');
        $location->setLabel('Địa điểm: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Địa điểm: '
            ]);
        $this->add($location);

        $trainer = new Element\Text('trainer');
        $trainer->setLabel('Người đào tạo: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Người đào tạo: '
            ]);
        $this->add($trainer);

        $participants = new Element\Select('participants');
        $participants->setLabel('Người tham gia: ')
            ->setAttributes([
                'id' => 'participants',
                'class' => 'select2 form-control',
                'multiple' => 'multiple',
                'placeholder'   => 'Người tham gia: '
            ]);
        $this->add($participants);

        $content = new Element\Textarea('content');
            $content->setLabel('Nội dung: ')
                ->setAttributes([
                    'rows'       => 2,
                    'id' => 'content',
                    'class' => 'form-control',
                    'placeholder'   => 'Nội dung: '
                ]);
        $this->add($content);

        $document = new Element\Text('document');
        $document->setLabel('Tài liệu: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Tài liệu: '
            ]);
        $this->add($document);

        $fee = new Element\Text('fee');
        $fee->setLabel('Học phí: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Học phí: '
            ]);
        $this->add($fee);

        $otherInfo = new Element\Textarea('other_info');
            $otherInfo->setLabel('Thông tin liên quan khác: ')
                ->setAttributes([
                    'rows'       => 2,
                    'id' => 'other_info',
                    'class' => 'form-control',
                    'placeholder'   => 'Thông tin liên quan khác: '
                ]);
        $this->add($otherInfo);

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
                            NotEmpty::IS_EMPTY => "Tên khóa đào tạo không được để trống"
                        ]
                    ]
                ],
            ]
        ]);

        $inputFilter->add([
            'name' => 'participants',
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
                            NotEmpty::IS_EMPTY => "Phải chọn người tham gia!"
                        ]
                    ]
                ],
            ]
        ]);

        $inputFilter->add([
            'name' => 'date',
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
                            NotEmpty::IS_EMPTY => "Phải chọn ngày tham gia!"
                        ]
                    ]
                ],
            ]
        ]);
    }
}