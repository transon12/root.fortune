<?php
namespace Promotions\Form\Index;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class AddRandomForm extends Form{
    
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
        
        $limitWin = new Element\Text('limit_win');
        $limitWin->setLabel('Giới hạn số lần trúng thưởng (trong thời gian chạy chương trình): ')
            ->setAttributes([
                'id' => 'limit_win',
                'class' => 'form-control',
                'value' => '0',
                'placeholder'   => '0'
            ]);
        $this->add($limitWin);
        
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
        
        $messageLimitWin = new Element\Textarea('message_limit_win');
        $messageLimitWin->setLabel('Tin nhắn giới hạn trúng thưởng: ')
            ->setAttributes([
                'rows'       => 2,
                'id' => 'description',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn giới hạn trúng thưởng: '
            ]);
        $this->add($messageLimitWin);
        
        $isRandom = new Element\Radio('is_random');
        $isRandom->setLabel('Trúng thưởng ngẫu nhiên: ')
            ->setAttributes([
                'value'     => '0',
                'class'     => 'radio-col-brown',
                'style'     => 'margin-left: 10px; margin-right: 3px;'
            ])
            ->setValueOptions([
                'is_random_0' => [
                    'label' => 'Không cho chạy ngẫu nhiên', 
                    'label_attributes' => ['for' => 'is_random_0'], 
                    'value' => '0', 
                    'attributes' => ['id' => 'is_random_0']
                ],'is_random_1' => [
                    'label' => 'Cấp độ 1', 
                    'label_attributes' => ['for' => 'is_random_1'], 
                    'value' => '1', 
                    'attributes' => ['id' => 'is_random_1']
                ],'is_random_2' => [
                    'label' => 'Cấp độ 2', 
                    'label_attributes' => ['for' => 'is_random_2'], 
                    'value' => '2', 
                    'attributes' => ['id' => 'is_random_2']
                ],'is_random_3' => [
                    'label' => 'Cấp độ 3', 
                    'label_attributes' => ['for' => 'is_random_3'], 
                    'value' => '3', 
                    'attributes' => ['id' => 'is_random_3']
                ],'is_random_4' => [
                    'label' => 'Cấp độ 4', 
                    'label_attributes' => ['for' => 'is_random_4'], 
                    'value' => '4', 
                    'attributes' => ['id' => 'is_random_4']
                ]
            ]);
        $this->add($isRandom);
        
        $orderWin = new Element\Text('order_win');
        $orderWin->setLabel('Số thứ tự trúng thưởng: ')
            ->setAttributes([
                'id' => 'order_win',
                'class' => 'form-control',
                'placeholder'   => 'Số thứ tự trúng thưởng: '
            ]);
        $this->add($orderWin);
        
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
        
        
    }
}