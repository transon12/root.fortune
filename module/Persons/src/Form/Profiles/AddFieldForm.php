<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Persons\Form\Profiles;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Identical;
use Zend\Validator\EmailAddress;
use Zend\Validator\Hostname;

class AddFieldForm extends Form{
    
    public function __construct($route = null) {
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

        $name = new Element\Text('name');
        $name->setLabel("Tên trường: ")
            ->setAttributes([
                'class' => 'form-control',
                'placeholder' => 'Tên trường: ',
            ]);
        $this->add($name);

        $content = new Element\Text('content');
        $content->setLabel("Nội dung: ")
            ->setAttributes([
                'class' => 'form-control',
                'placeholder' => 'Nội dung: ',
            ]);
        $this->add($content);
        
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
        
        $inputFilter->add([
            'name' => 'content',
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