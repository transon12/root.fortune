<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Persons\Form\LeaveApplies;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Identical;
use Zend\Validator\EmailAddress;
use Zend\Validator\Hostname;

class AddForm extends Form{
    
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

        $leaveDayRemain = new Element\Text('leave_day_remain');
        $leaveDayRemain->setLabel("Số ngày phép còn lại: ")
            ->setAttributes([
                'id' => 'leave_day_remain',
                'class' => 'form-control',
                'placeholder' => 'Số ngày phép còn lại: ',
                'disabled' => true,
            ]);
        $this->add($leaveDayRemain);

        $leaveStartDate = new Element\Text('leave_start_date');
        $leaveStartDate->setLabel('Nghỉ từ ngày: ')
            ->setAttributes([
                'id' => 'leave_start_date',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Nghỉ từ ngày: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($leaveStartDate);

        $optionLeaveStartDate = new Element\Select('option_leave_start_date');
        $optionLeaveStartDate->setLabel('Chọn')
                ->setAttributes([
                    'id' => 'option_leave_start_date',
                    'value'     => '0',
                    'class' => 'form-control',
                ])
                ->setValueOptions([
                '0' => 'Cả ngày',
                '1' => 'Sáng',
                '2' => 'Chiều'
                ]);
        $this->add($optionLeaveStartDate);

        $optionLeaveStopDate = new Element\Select('option_leave_stop_date');
        $optionLeaveStopDate->setLabel('Chọn')
                ->setAttributes([
                    'id' => 'option_leave_stop_date',
                    'value'     => '0',
                    'class' => 'form-control',
                ])
                ->setValueOptions([
                '0' => 'Cả ngày',
                '1' => 'Sáng',
                '2' => 'Chiều'
                ]);
        $this->add($optionLeaveStopDate);
        
        $leaveStopDate = new Element\Text('leave_stop_date');
        $leaveStopDate->setLabel('Nghỉ tới ngày: ')
            ->setAttributes([
                'id' => 'leave_stop_date',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Nghỉ tới ngày: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($leaveStopDate);

        $sendTo = new Element\Select('send_to');
        $sendTo->setLabel('Gửi tới: ')
            ->setAttributes([
                'id' => 'send_to',
                'class' => 'select2 form-control',
                'multiple' => 'multiple',
                'placeholder'   => 'Gửi tới: '
            ]);
        $this->add($sendTo);

        $reason = new Element\Textarea('reason');
            $reason->setLabel('Lý do nghỉ: ')
                ->setAttributes([
                    'rows'       => 2,
                    'id' => 'reason',
                    'class' => 'form-control',
                    'placeholder'   => 'Lý do nghỉ: '
                ]);
        $this->add($reason);
        
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
            'name' => 'leave_start_date',
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
            'name' => 'leave_stop_date',
            'required' => false,
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