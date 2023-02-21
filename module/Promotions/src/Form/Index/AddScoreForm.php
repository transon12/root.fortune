<?php
namespace Promotions\Form\Index;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Digits;

class AddScoreForm extends Form{
    
    private $action;
    
    public function __construct() {
        parent::__construct();
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form',
            'enctype' => 'multipart/form-data'
        ]);
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){
        $name = new Element\Text('name');
        $name->setLabel('Tên chương trình: ')
            ->setAttributes([
                'id' => 'name',
                'class' => 'form-control',
                'placeholder'   => 'Tên chương trình: '
            ]);
        $this->add($name);
        
        $datetimeBegin = new Element\Text('datetime_begin');
        $datetimeBegin->setLabel('Thời gian bắt đầu: ')
            ->setAttributes([
                'id' => 'datetime_begin',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Thời gian bắt đầu: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($datetimeBegin);
        
        $datetimeEnd = new Element\Text('datetime_end');
        $datetimeEnd->setLabel('Thời gian kết thúc: ')
            ->setAttributes([
                'id' => 'datetime_end',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Thời gian kết thúc: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($datetimeEnd);
        
        $description = new Element\Textarea('description');
        $description->setLabel('Mô tả thông tin: ')
            ->setAttributes([
                'rows'       => 5,
                'id' => 'description',
                'class' => 'form-control',
                'placeholder'   => 'Mô tả thông tin: '
            ]);
        $this->add($description);
        
        $status = new Element\Radio('status');
        $status->setLabel('Trạng thái: ')
            ->setAttributes([
                'value'     => '1',
                'class'     => 'radio-col-brown',
                'style'     => 'margin-left: 10px; margin-right: 3px;'
            ])
            ->setValueOptions([
                'yes' => [
                    'label' => 'Hoạt động', 
                    'label_attributes' => ['for' => 'yes'], 
                    'value' => '1', 
                    'attributes' => ['id' => 'yes']
                ],'no' => [
                    'label' => 'Không hoạt động', 
                    'label_attributes' => ['for' => 'no'], 
                    'value' => '0', 
                    'attributes' => ['id' => 'no']
                ]
            ]);
        $this->add($status);
        
        $dial = new Element\Radio('dial');
        $dial->setLabel('Tham gia quay số: ')
            ->setAttributes([
                'value'     => '1',
                'class'     => 'radio-col-brown',
                'style'     => 'margin-left: 10px; margin-right: 3px;'
            ])
            ->setValueOptions([
                'dial_yes' => [
                    'label' => 'Có', 
                    'label_attributes' => ['for' => 'dial_yes'], 
                    'value' => '1', 
                    'attributes' => ['id' => 'dial_yes']
                ],'dial_no' => [
                    'label' => 'Không', 
                    'label_attributes' => ['for' => 'dial_no'], 
                    'value' => '0', 
                    'attributes' => ['id' => 'dial_no']
                ]
            ]);
        $this->add($dial);
        

		
		$suppliersId = new Element\Select('supplier_id');
        $suppliersId->setLabel('Nhân Viên: ')
            ->setAttributes([
                'class' => 'select2 form-control',
                'placeholder'   => 'Nhân Viên: '
            ]);
        $this->add($suppliersId);
		
		
		
		
		
		
		
		
		
		
		
		
		
		
        $limitWin = new Element\Text('limit_win');
        $limitWin->setLabel('Giới hạn số lần trúng thưởng (trong thời gian chạy chương trình): ')
            ->setAttributes([
                'id' => 'limit_win',
                'class' => 'form-control',
                'value' => '0',
                'placeholder'   => '0'
            ]);
        $this->add($limitWin);
        
        $limitMessageDay = new Element\Text('limit_message_day');
        $limitMessageDay->setLabel('Giới hạn số lần nhắn trong ngày: ')
            ->setAttributes([
                'id' => 'limit_message_day',
                'class' => 'form-control',
                'value' => '0'
            ]);
        $this->add($limitMessageDay);
        
        $messageLimitDay = new Element\Textarea('message_limit_day');
        $messageLimitDay->setLabel('Tin nhắn giới hạn trong ngày: ')
            ->setAttributes([
                'rows'       => 2,
                'id' => 'message_limit_day',
                'class' => 'form-control',
                'value'   => 'Tin nhắn giới hạn trong ngày'
            ]);
        $this->add($messageLimitDay);
        
        $limitMessageMonth = new Element\Text('limit_message_month');
        $limitMessageMonth->setLabel('Giới hạn số lần nhắn trong tháng: ')
            ->setAttributes([
                'id'            => 'limit_message_month',
                'class'         => 'form-control',
                'value'         => '0'
            ]);
        $this->add($limitMessageMonth);
        
        $messageLimitMonth = new Element\Textarea('message_limit_month');
        $messageLimitMonth->setLabel('Tin nhắn giới hạn trong tháng: ')
            ->setAttributes([
                'rows'      => 2,
                'id'        => 'message_limit_month',
                'class'     => 'form-control',
                'value'     => 'Tin nhắn giới hạn trong tháng'
            ]);
        $this->add($messageLimitMonth);
        
        $scoreWin = new Element\Text('score_win');
        $scoreWin->setLabel('Tổng điểm trúng thưởng: ')
            ->setAttributes([
                'id' => 'score_win',
                'class' => 'form-control',
                'value' => '0',
                'placeholder'   => '0'
            ]);
        $this->add($scoreWin);
        
        $priceTopup = new Element\Select('price_topup');
        $priceTopup->setLabel('Số tiền trúng thưởng: ')
            ->setAttributes([
                'class' => 'form-control',
                'value' => '0'
            ])
            ->setValueOptions(\Admin\Service\Digital::returnPriceTopup());
        $this->add($priceTopup);
        
        $messageNearWin = new Element\Textarea('message_near_win');
        $messageNearWin->setLabel('Tin nhắn gần trúng: ')
            ->setAttributes([
                'rows'       => 2,
                'id' => 'message_near_win',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn gần trúng: '
            ]);
        $this->add($messageNearWin);
        
        $messageWin = new Element\Textarea('message_win');
        $messageWin->setLabel('Tin nhắn trúng thưởng: ')
            ->setAttributes([
                'rows'       => 2,
                'id' => 'message_win',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn trúng thưởng: '
            ]);
        $this->add($messageWin);
        
        $messageLimitWin = new Element\Textarea('message_limit_win');
        $messageLimitWin->setLabel('Tin nhắn giới hạn trúng thưởng: ')
            ->setAttributes([
                'rows'       => 2,
                'id' => 'message_limit_win',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn giới hạn trúng thưởng: '
            ]);
        $this->add($messageLimitWin);
        
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
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => "Tên chương trình không được để trống!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 2,
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
            'name' => 'datetime_begin',
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
                            NotEmpty::IS_EMPTY => "Thời gian bắt đầu không được để trống!"
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
        
        $inputFilter->add([
            'name' => 'datetime_end',
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
                            NotEmpty::IS_EMPTY => "Thời gian kết thúc không được để trống!"
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
        
        $inputFilter->add([
            'name' => 'score_win',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'limit_win',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags']
            ],
            'validators' => [[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 4,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
                        ]
                    ]
                ],[
                    'name' => 'Digits',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'messages' => [
                            Digits::NOT_DIGITS => "Bắt buộc phải là số!"
                        ]
                    ]
                ],
            ]
        ]);
        
        
    }
}