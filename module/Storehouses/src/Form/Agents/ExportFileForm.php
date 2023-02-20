<?php

namespace Storehouses\Form\Agents;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\File\Size;
use Zend\Validator\File\MimeType;
use Zend\Validator\File\Upload;
use Zend\Validator\File\UploadFile;

class ExportFileForm extends Form{
    
    public function __construct($route = '') {
        parent::__construct();
        
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
        
        $file = new Element\File('file');
        $file->setLabel('File: ')
            ->setAttributes([
                'id' => 'file'
            ]);
        $this->add($file);
        
        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Nhập file'
            ]
        ]);
        
    }
    
    private function validatorForm(){
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        
        $inputFilter->add([
//             'type'     => 'Zend\InputFilter\FileInput',
            'name'     => 'file',
            'required' => true,
            'validators' => [/* [
                   // 'name' => 'Zend\Validator\File\Upload',
                    'options' => [
                        'messages' => [
                            UploadFile::NO_FILE => "Bắt buộc phải chọn file!"
                        ]
                    ]
                ], */[
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
                    'max'  => 1024*100000,
                    
                    'messages' => [
                        Size::TOO_BIG => "File quá lớn, chỉ cho phép file dưới %max%"
                    ]
                ]
            ],
            ]
        ]);
        
    }
}