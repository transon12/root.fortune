<?php

namespace Persons\Form\Index;

use Zend\Db\Sql\Predicate\NotIn;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\File\Extension;
use Zend\Validator\File\Size;
use Zend\Validator\File\MimeType;
use Zend\Validator\File\UploadFile;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class UploadFileForm extends Form{
    
    public function __construct($route = '') {
        parent::__construct();
        
        $this->setAttributes([
            // 'class' => 'form-horizontal row',
            // 'enctype' => 'multipart/form-data',
            // // 'method' => 'POST',
            
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
        
        $file = new Element\File('file');
        $file->setLabel('File upload: ')
            ->setAttributes([
                "id" => "file"
            ]);
        $this->add($file);

        $name = new Element\Text('name');
        $name->setLabel('Nhập tiêu đề:')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder' => 'Nhập tiêu đề'
            ]);
        $this->add($name);

        $departmentId = new Element\Select('group_id');
        $departmentId->setLabel('Chọn nơi upload file: ')
            ->setAttributes([
                'id'     => 'group',
                'class' => 'form-control',
                ]);
            
        $this->add($departmentId);
        
        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'id' => 'btn-upload',
                'class' => 'btn btn-success',
                'value' => 'Lưu'
            ]
        ]);
    }
    
    private function validatorForm(){
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        
        $inputFilter->add([
            'name' => 'file',
            'required' => true,
            'validators' => [
                [
                   // 'name' => \Zend\Validator\File\Extension::class,
                    'name' => 'Zend\Validator\File\Extension',
                    'options' => [
                    'extension' => ['jpg','png','jpeg', 'pdf'],
                    'case' => false, //không phân biệt HOA/thường
                    'messages' => [
                        Extension::FALSE_EXTENSION => "File upload không đúng định dạng"
                        ],
                    ],
                ],
                [
                    'name'    => 'FileSize',
                    'options' => [
                        'max'  => 1024*1024*10,
                        'messages' => [
                            Size::TOO_BIG => "File quá lớn, chỉ cho phép file dưới %max%"
                        ]
                    ]
                ],
                
            ]
        ]);

        // $inputFilter->add([
        //     'name' => 'name',
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
        //                     NotEmpty::IS_EMPTY => "Tiêu đề không được để trống"
        //                 ]
        //             ]
        //         ],
        //         [
        //             'name' => 'StringLength',
        //             'break_chain_on_failure' => true,
        //             'options' => [
        //                 'min' => 2,
        //                 'max' => 32,
        //                 'messages' => [
        //                     StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự",
        //                     StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ký tự"
        //                 ]
        //             ]
        //         ],
        //     ]
        // ]);
        
    }
}