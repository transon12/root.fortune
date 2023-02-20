<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Codes\Form\Index;

use Zend\Form\Form;

class AddPinForm extends Form{
    
    public function __construct($route = '') {
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form-modal',
            'route' => $route,
            'enctype' => 'multipart/form-data'
        ]);
        
        $this->setElements();
    }
    
    private function setElements(){
        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Thêm mã PIN'
            ]
        ]);
    }
}