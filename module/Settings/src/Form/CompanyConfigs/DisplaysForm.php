<?php

namespace Settings\Form\CompanyConfigs;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Digits;

class DisplaysForm extends Form{
    
    public function __construct($route = '') {
        parent::__construct();
        
        $this->setAttributes([
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
        
        $logo = new Element\File('logo');
        $logo->setLabel('Logo: ')
            ->setAttributes([
                'id' => 'logo'
            ]);
        $this->add($logo);
        
        $style = new Element\Textarea('style');
        $style->setLabel('Cấu hình css: ')
            ->setAttributes([
                'id' => 'style',
                'class' => 'form-control',
                'placeholder'   => 'Cấu hình css: '
            ]);
        $this->add($style);
        
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
        
    }
}