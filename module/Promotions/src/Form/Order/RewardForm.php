<?php

namespace Promotions\Form\Order;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Digits;

class RewardForm extends Form
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
        $codeOrder = new Element\Text('code_order');
        $codeOrder->setLabel("Mã đơn hàng trả thưởng: ")
            ->setAttributes([
                'id' => 'code_order',
                'class' => 'form-control',
                'placeholder'   => 'Mã đơn hàng trả thưởng: '
            ]);
        $this->add($codeOrder);
        
        $finishedAt = new Element\Text('finished_at');
        $finishedAt->setLabel("Thời gian trả thưởng (nếu để trống sẽ lấy thời gian hiện tại): ")
            ->setAttributes([
                'id' => 'datetime_begin',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Thời gian trả thưởng: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($finishedAt);

        // $isFinish = new Element\Checkbox('is_finish');
        // $isFinish->setLabel('Đã trả thưởng: ')
        //     ->setAttributes([
        //         'id' => 'is_finish'
        //     ]);
        // $this->add($isFinish);

        $note3 = new Element\Textarea('note_3');
        $note3->setLabel('Ghi chú trả thưởng: ')
            ->setAttributes([
                'id'        => 'note_3',
                'class'     => 'form-control',
                'rows'      => '5'
            ]);
        $this->add($note3);

        $this->add([
            'name'  => 'btnSubmit',
            'type'  => 'submit',
            'attributes'    => [
                'class' => 'btn btn-success',
                'value' => 'Trả thưởng'
            ]
        ]);
    }

    private function validatorForm()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
            'name' => 'code_order',
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
                            NotEmpty::IS_EMPTY => "'Mã đơn hàng' không được để trống!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 2,
                        'max' => 32,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui long nhap duoi %max% ky tu",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ky tu"
                        ]
                    ]
                ]
            ]
        ]);

    }
}
