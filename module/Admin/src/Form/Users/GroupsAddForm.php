<?php

namespace Admin\Form\Users;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Identical;

class GroupsAddForm extends Form{
    
    public function __construct($route = '') {
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form-groups',
            'route' => $route,
            'enctype' => 'multipart/form-data',
            'data-target' => '#large'
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){

        $groupsId = new Element\Select('groups_id');
        $groupsId->setLabel('Phòng ban: ')
        ->setAttributes([
            'class' => 'form-control',
            'placeholder'   => 'Phòng ban: '
        ]);
        $this->add($groupsId);

        $positionsId = new Element\Select('positions_id');
        $positionsId->setLabel('Chức vụ: ')
        ->setAttributes([
            'class' => 'form-control',
            'placeholder'   => 'Chức vụ: '
        ]);
        $this->add($positionsId);
        
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