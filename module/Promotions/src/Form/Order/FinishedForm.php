<?php

namespace Promotions\Form\Order;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Digits;

class FinishedForm extends Form
{

    private $action;

    public function __construct($route = '')
    {
        parent::__construct();

        $this->setAttributes([
            'class'     => 'form-horizontal row',
            'id'        => 'event-form',
            'enctype'   => 'multipart/form-data',
            'method'    => 'POST'
        ]);

        $this->setElements();
        $this->validatorForm();
    }

    private function setElements()
    {
        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-danger',
                'value' => 'Hoàn đơn'
            ]
        ]);
    }

    private function validatorForm()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
    }
}
