<?php
namespace Application\Form\Index;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class Layout10Form extends Form{
    
    private $action;
    
    public function __construct() {
        parent::__construct();
        $this->setAttributes([
            'class' => 'login100-form validate-form',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data'
        ]);
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){
        
        
        $code = new Element\Text('code');
        $code->setAttributes([
                'id' => 'code',
                'class' => 'input100',
                'placeholder'   => 'MÃ DƯỚI LỚP PHỦ CÀO: '
            ]);
        $this->add($code);
        
        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                // 'class' => 'login100-form-btn',
                'class' => 'btn btn-info btn-block',
                'value' => 'XÁC THỰC'
            ]
        ]);
    }
    
    private function validatorForm(){
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        
        
        $inputFilter->add([
            'name' => 'code',
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
                            NotEmpty::IS_EMPTY => "Mã dưới lớp phủ cào không được để trống!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 7,
                        'max' => 16,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui long nhap duoi %max% ky tu",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ky tu"
                        ]
                    ]
                ],
            ]
        ]);
    }
}