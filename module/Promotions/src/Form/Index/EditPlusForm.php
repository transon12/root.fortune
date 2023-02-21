<?php
namespace Promotions\Form\Index;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class EditPlusForm extends Form{

    private $action;

    public function __construct() {
        parent::__construct();
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data',
            'action' => ''
        ]);
        $this->setElements();
        $this->validatorForm();
    }

    private function setElements(){
        $name = new Element\Text('score');
        $name->setLabel('Số điểm thắng: ')
            ->setAttributes([
                'id' => 'score',
                'class' => 'form-control',
                'placeholder'   => 'Số điểm thắng: '
            ]);
        $this->add($name);



        $message_win = new Element\Textarea('message_win');
        $message_win->setLabel('Tin nhắn trúng thưởng: ')
            ->setAttributes([
                'rows'       => 5,
                'id' => 'message_win',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn trúng thưởng: '
            ]);
        $this->add($message_win);

        $priceTopup = new Element\Select('price_topup');
        $priceTopup->setLabel('Số tiền trúng thưởng: ')
            ->setAttributes([
                'class' => 'form-control',
                'value' => '0'
            ])
            ->setValueOptions(\Admin\Service\Digital::returnPriceTopup());
        $this->add($priceTopup);

        $messageWin = new Element\Textarea('message_win');
        $messageWin->setLabel('Tin nhắn trúng thưởng: ')
            ->setAttributes([
                'rows'       => 2,
                'id' => 'description',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn trúng thưởng: '
            ]);
        $this->add($messageWin);


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
            'name' => 'score',
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
                            NotEmpty::IS_EMPTY => "Số điểm không được để trống!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 1,
                        'max' => 128,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui long nhap duoi %max% ky tu",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ky tu"
                        ]
                    ]
                ],
            ]
        ]);

        $inputFilter->add([
            'name' => 'message_win',
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
                            NotEmpty::IS_EMPTY => "Tin nhắn trúng thưởng không được để trống!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 10,
                        'max' => 128,
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