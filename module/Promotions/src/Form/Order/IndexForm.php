<?php

namespace Promotions\Form\Order;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;

class IndexForm extends Form
{

    private $action;

    public function __construct($route = '')
    {
        parent::__construct();

        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data',
            'method' => 'POST'
        ]);

        $this->setElements();
        $this->validatorForm();
    }

    private function setElements()
    {

        $userInputCrm = new Element\Text('user_input_crm');
        $userInputCrm->setLabel('Tên NPT (CRM): ')
            ->setAttributes([
                'id' => 'user_input_crm',
                'class' => 'form-control',
                'readonly' => 'readonly'
            ]);
        $this->add($userInputCrm);

        $userInput = new Element\Select('user_input');
        $userInput->setLabel('User phụ trách (CHG): ')
            ->setAttributes([
                'id' => 'user_input',
                'class' => 'form-control'
            ]);
        $this->add($userInput);

        $userInputId = new Element\Hidden("user_input_id");
        $userInputId->setAttributes(["id" => "user_input_id"]);
        $this->add($userInputId);

        $userInputName = new Element\Hidden("user_input_name");
        $userInputName->setAttributes(["id" => "user_input_name"]);
        $this->add($userInputName);

        $source = new Element\Select('source');
        $source->setLabel('Nguồn khách hàng: ')
            ->setAttributes([
                'id' => 'source',
                'class' => 'form-control'
            ])
            ->setValueOptions(array_merge(["0" => "--- Chọn một nguồn ---"], \Admin\Service\Promotion::returnSource()));
        $this->add($source);

        $note1 = new Element\Textarea('note_1');
        $note1->setLabel('Ghi chú chuyển dữ liệu: ')
            ->setAttributes([
                'id'        => 'note_1',
                'class'     => 'form-control',
                'rows'      => '5'
            ]);
        $this->add($note1);

        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Chuyển thông tin cho người phụ trách'
            ]
        ]);
    }

    private function validatorForm()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
            'name' => 'user_input',
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
                            NotEmpty::IS_EMPTY => "Phải chọn một tài khoản xử lý!"
                        ]
                    ]
                ],
            ]
        ]);

        $inputFilter->add([
            'name' => 'source',
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
                            NotEmpty::IS_EMPTY => "Phải chọn một nguồn!"
                        ]
                    ]
                ],
            ]
        ]);
    }
}
