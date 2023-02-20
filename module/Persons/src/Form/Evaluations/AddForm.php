<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Persons\Form\Evaluations;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\Digits;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class AddForm extends Form{
    
    private $action;
    private $dataEvaluation;
    private $userId;
    
    public function __construct($dataEvaluation = null) {
        $this->dataEvaluation = $dataEvaluation;
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data'
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){

        $year = new Element\Text('year');
        $year->setLabel('Đánh giá năm: ')
            ->setAttributes([
                'id' => 'get_year',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Đánh giá năm: ',
                'data-toggle' => 'datetimepicker',
                'autocomplete' => 'off',
            ]);
        $this->add($year);

        // if(!empty($this->dataEvaluation)){
        //     foreach($this->dataEvaluation as $key => $item){
        //         $dataPoint = new Element\Select($key."[point]");
        //         $dataPoint->setLabel($item['name'] . ":")
        //             ->setAttributes([
        //                 // 'id' => '',
        //                 'class' => 'title_tooltip form-control',
        //                 // 'data-toggle'=>'tooltip',
        //                 // 'data-placement'=>'left',
        //             ]);
        //         $dataPoint->setValueOptions([
        //             [
        //                 'value' => 'NA',
        //                 'label' => 'NA',
        //                 'attributes' => [
        //                     'title'=>'Không áp dụng',
        //                     // 'class' => 'active',
        //                 ],
        //             ],
        //             [
        //                 'value' => '1',
        //                 'label' => '1',
        //                 'attributes' => [
        //                     'title'=>'Không đạt',
        //                 ],
        //             ],
        //             [
        //                 'value' => '2',
        //                 'label' => '2',
        //                 'attributes' => [
        //                     'title'=>'Đạt',
        //                 ],
        //             ],
        //             [
        //                 'value' => '3',
        //                 'label' => '3',
        //                 'attributes' => [
        //                     'title'=>'Tốt',
        //                 ],
        //             ],
        //             [
        //                 'value' => '4',
        //                 'label' => '4',
        //                 'attributes' => [
        //                     'title'=>'Rất tốt',
        //                 ],
        //             ],
        //             [
        //                 'value' => '5',
        //                 'label' => '5',
        //                 'attributes' => [
        //                     'title'=>'Xuất sắc',
        //                 ],
        //             ],
        //         ]);
        //         $this->add($dataPoint);

                // $expression = new Element\Textarea($key."[expression]");
                // $expression->setLabel("Biểu hiện ")
                //     ->setAttributes([
                //         'class' => 'form-control',
                //         'placeholder' => 'Biểu hiện ',
                //         // 'value' => $valuePost[$key]["expression"] !==null ? $valuePost[$key]["expression"] : '' ,
                //     ]);
                // $this->add($expression);
        //     }
        // }

        // $generalComment = new Element\Textarea('general_comment');
        // $generalComment->setLabel("Nhận xét chung: ")
        //     ->setAttributes([
        //         'id' => 'general_comment',
        //         'class' => 'form-control',
        //         'placeholder' => 'Nhận xét chung: ',
        //     ]);
        // $this->add($generalComment);

        $generalPersonal = new Element\Textarea('personal_comment');
        $generalPersonal->setLabel("Nhận xét của nhân viên: ")
            ->setAttributes([
                'id' => 'personal_comment',
                'class' => 'form-control',
                'placeholder' => 'Nhận xét của nhân viên: ',
            ]);
        $this->add($generalPersonal);
        
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
            'name' => 'year',
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
                            NotEmpty::IS_EMPTY => "Hãy chọn năm!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 4,
                        'max' => 4,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ký tự"
                        ]
                    ]
                ],
                [
                    'name' => 'Digits',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'messages' => [
                            Digits::NOT_DIGITS => "Vui lòng nhập năm là số !"
                        ]
                    ]
                ]
            ]
        ]);
        
        if(!empty($this->dataEvaluation)){
            foreach($this->dataEvaluation as $key => $item){
                $inputFilter->add([
                    'name' => $key."[point]",
                    'required' => false,
                    'filter' => [
                        ['name' => 'StringTrim'],
                        ['name' => 'StripTags']
                    ],
                ]);
            }
        }
    }
}