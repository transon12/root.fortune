<?php

namespace Settings\Form\Phones;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\File\Size;
use Zend\Validator\File\MimeType;

class ImportForm extends Form{
    
    private $action;
    
    public function __construct($action = 'index') {
        $this->action = $action;
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data',
            'style' => 'border: 1px solid #CCC; padding: 5px; width: 100%'
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){
        
        $fileImport = new Element\File('file_import');
        $fileImport->setLabel('Import file: ')
            ->setAttributes([
                'id' => 'file_import'
            ]);
        $this->add($fileImport);
        
        $this->add([
            'name'  => 'btnImport',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Thêm bằng file'
            ]
        ]);
        
        $this->add([
            'name'  => 'btnSample',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-secondary',
                'value' => 'File mẫu'
            ]
        ]);
    }
    
    private function validatorForm(){
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        
        $inputFilter->add([
            'type'     => 'Zend\InputFilter\FileInput',
            'name'     => 'file_import',
            'required' => false,
            'validators' => [[
                'name'    => 'Zend\Validator\File\MimeType',
                'options' => [
                    'mimeType'  => [
                        //'image/jpeg', 'image/png', 'image/jpg', 'image/gif'
                        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ],
                    'messages' => [
                        MimeType::FALSE_TYPE => "Định dạng %type% không cho phép!",
                        MimeType::NOT_DETECTED => "Mimetype không xác định",
                        MimeType::NOT_READABLE => "Mimetype không thể đọc"
                    ]
                ]
            ],[
                'name'    => 'FileSize',
                'options' => [
                    'max'  => 1024*10000,
                    'messages' => [
                        Size::TOO_BIG => "File quá lớn, chỉ cho phép file dưới %max%"
                    ]
                ]
            ],
            ]
        ]);
        
    }
}