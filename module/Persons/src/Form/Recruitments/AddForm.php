<?php

namespace Persons\Form\Recruitments;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Digits;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class AddForm extends Form{

    public function __construct($route = ''){
        parent::__construct();

        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form-modal',
            'route' => $route,
            'enctype' => 'multipart/form-data',
            'data-target' => '#defaultSize',
        ]);

        $this->setElements();
        $this->validatorForm();
    }

    private function setElements(){
        
        $position = new Element\Text('position');
        $position->setLabel('Vị trí: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Vị trí: '
            ]);
        $this->add($position);

        $department = new Element\Select('group_id');
        $department->setLabel('Bộ phận tuyển dụng: ')
            ->setAttributes([
                'id' => 'group_id',
                'class' => 'form-control',
                'placeholder'   => 'Bộ phận tuyển dụng: '
            ]);
        $this->add($department);

        $amount = new Element\Text('amount');
        $amount->setLabel('Số lượng: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Số lượng: '
            ]);
        $this->add($amount);

        $date_recruitment = new Element\Text('date_recruitment');
        $date_recruitment->setLabel('Thời gian cần nhân sự: ')
            ->setAttributes([
                'id' => 'date_recruitment',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Thời gian cần nhân sự: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($date_recruitment);

        $description = new Element\Textarea('description');
        $description->setLabel('Mô tả công việc: ')
            ->setAttributes([
                'rows' => 2,
                'id' => 'description',
                'class' => 'form-control',
                'placeholder'   => 'Mô tả công việc: '
            ]);
        $this->add($description);

        $require = new Element\Textarea('require');
        $require->setLabel('Yêu cầu tuyển dụng: ')
            ->setAttributes([
                'rows' => 2,
                'id' => 'require',
                'class' => 'form-control',
                'placeholder'   => 'Yêu cầu tuyển dụng: '
            ]);
        $this->add($require);

        $expectedSalary = new Element\Text('expected_salary');
        $expectedSalary->setLabel('Mức lương dự kiến: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Mức lương dự kiến: '
            ]);
        $this->add($expectedSalary);

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
            'name' => 'date_recruitment',
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