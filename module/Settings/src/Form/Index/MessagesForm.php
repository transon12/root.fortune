<?php

namespace Settings\Form\Index;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class MessagesForm extends Form{
    
    private $userId;
    
    public function __construct($userId = null) {
        $this->userId = $userId;
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
        
        if($this->userId == '1'){
            $companyId = new Element\Select('company_id');
            $companyId->setLabel('Công ty: ')
                ->setAttributes([
                    'id' => 'company_id',
                    'class' => 'form-control',
                    'placeholder'   => 'Công ty: '
                ]);
            $this->add($companyId);
        }
        
        $messageSuccess = new Element\Text('message_success');
        $messageSuccess->setLabel('Tin nhắn thành công: ')
            ->setAttributes([
                'id' => 'message_success',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn thành công: '
            ]);
        $this->add($messageSuccess);
        
        $messageInvalid = new Element\Text('message_invalid');
        $messageInvalid->setLabel('Tin nhắn sai mã: ')
            ->setAttributes([
                'id' => 'message_invalid',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn sai mã: '
            ]);
        $this->add($messageInvalid);
        
        $messageChecked = new Element\Text('message_checked');
        $messageChecked->setLabel('Tin nhắn đã kiểm tra: ')
            ->setAttributes([
                'id' => 'message_checked',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn đã kiểm tra: '
            ]);
        $this->add($messageChecked);
        
        $messageOutdate = new Element\Text('message_outdate');
        $messageOutdate->setLabel('Tin nhắn hết hạn: ')
            ->setAttributes([
                'id' => 'message_outdate',
                'class' => 'form-control',
                'placeholder'   => 'Tin nhắn hết hạn: '
            ]);
        $this->add($messageOutdate);
        
//         $messageSuccessAgent = new Element\Text('message_success_agent');
//         $messageSuccessAgent->setLabel('Tin nhắn thành công của đại lý: ')
//             ->setAttributes([
//                 'class' => 'form-control',
//                 'placeholder'   => 'Tin nhắn thành công của đại lý: '
//             ]);
//         $this->add($messageSuccessAgent);
        
//         $messageInvalidAgent = new Element\Text('message_invalid_agent');
//         $messageInvalidAgent->setLabel('Tin nhắn sai mã của đại lý: ')
//             ->setAttributes([
//                 'class' => 'form-control',
//                 'placeholder'   => 'Tin nhắn sai mã của đại lý: '
//             ]);
//         $this->add($messageInvalidAgent);
        
//         $messageCheckedAgent = new Element\Text('message_checked_agent');
//         $messageCheckedAgent->setLabel('Tin nhắn đã kiểm tra của đại lý: ')
//             ->setAttributes([
//                 'class' => 'form-control',
//                 'placeholder'   => 'Tin nhắn đã kiểm tra của đại lý: '
//             ]);
//         $this->add($messageCheckedAgent);
        
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

        if($this->userId == '1'){
            $inputFilter->add([
                'name' => 'company_id',
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
                            NotEmpty::IS_EMPTY => "Phải chọn một công ty!"
                        ]
                    ]
                ],]
            ]);
        }
        
        $inputFilter->add([
            'name' => 'message_success',
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
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 255,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'message_invalid',
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
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 255,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
        
        $inputFilter->add([
            'name' => 'message_checked',
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
                ],[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'max' => 255,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
                        ]
                    ]
                ],
            ]
        ]);
        
//         $inputFilter->add([
//             'name' => 'message_success_agent',
//             'required' => true,
//             'filters' => [
//                 ['name' => 'StringTrim'],
//                 ['name' => 'StripTags']
//             ],
//             'validators' => [[
//                     'name' => 'NotEmpty',
//                     'break_chain_on_failure' => true,
//                     'options' => [
//                         'messages' => [
//                             NotEmpty::IS_EMPTY => "Không được để trống!"
//                         ]
//                     ]
//                 ],[
//                     'name' => 'StringLength',
//                     'break_chain_on_failure' => true,
//                     'options' => [
//                         'max' => 255,
//                         'messages' => [
//                             StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
//                         ]
//                     ]
//                 ],
//             ]
//         ]);
        
//         $inputFilter->add([
//             'name' => 'message_invalid_agent',
//             'required' => true,
//             'filters' => [
//                 ['name' => 'StringTrim'],
//                 ['name' => 'StripTags']
//             ],
//             'validators' => [[
//                     'name' => 'NotEmpty',
//                     'break_chain_on_failure' => true,
//                     'options' => [
//                         'messages' => [
//                             NotEmpty::IS_EMPTY => "Không được để trống!"
//                         ]
//                     ]
//                 ],[
//                     'name' => 'StringLength',
//                     'break_chain_on_failure' => true,
//                     'options' => [
//                         'max' => 255,
//                         'messages' => [
//                             StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
//                         ]
//                     ]
//                 ],
//             ]
//         ]);
        
//         $inputFilter->add([
//             'name' => 'message_checked_agent',
//             'required' => true,
//             'filters' => [
//                 ['name' => 'StringTrim'],
//                 ['name' => 'StripTags']
//             ],
//             'validators' => [[
//                     'name' => 'NotEmpty',
//                     'break_chain_on_failure' => true,
//                     'options' => [
//                         'messages' => [
//                             NotEmpty::IS_EMPTY => "Không được để trống!"
//                         ]
//                     ]
//                 ],[
//                     'name' => 'StringLength',
//                     'break_chain_on_failure' => true,
//                     'options' => [
//                         'max' => 255,
//                         'messages' => [
//                             StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
//                         ]
//                     ]
//                 ],
//             ]
//         ]);

    }
}