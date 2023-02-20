<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Storehouses\Form\Products;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class AddForm extends Form{
    
    private $action;
    private $settingDatas;
    private $userId;
    
    public function __construct($action = 'add', $settingDatas = null, $userId = null) {
        $this->action = $action;
        $this->settingDatas = $settingDatas;
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
        
        $code = new Element\Text('code');
        $code->setLabel('Mã sản phẩm: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Mã sản phẩm: '
            ]);
        $this->add($code);
        
        $barcode = new Element\Text('barcode');
        $barcode->setLabel('Barcode sản phẩm: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Barcode sản phẩm: '
            ]);
        $this->add($barcode);
        
        $name = new Element\Text('name');
        $name->setLabel('Tên sản phẩm: ')
            ->setAttributes([
                'class' => 'form-control',
                'placeholder'   => 'Tên sản phẩm: '
            ]);
        $this->add($name);

        $description = new Element\Textarea('description');
        $description->setLabel('Mô tả sản phẩm')
            ->setAttributes([
                'id'            => 'description',
                'class'         => 'form-control',
                'placeholder'   => 'Mô tả sản phẩm'
            ]);
        $this->add($description);
            
        if(!empty($this->settingDatas)){
            foreach($this->settingDatas as $key => $item){
                if($item['type'] == "Textarea"){
                    $data = new Element\Textarea($key);
                    $data->setLabel($item['name'])
                    ->setAttributes([
                        'rows'          => 2,
                        'id'            => $key,
                        'class'         => 'form-control',
                        'placeholder'   => $item['name']
                    ]);
                    $this->add($data);
                }elseif($item['type'] == "File" || $item['type'] == "Image"){
        
                    $data = new Element\Hidden($key . '_hidden');
                    $data->setLabel('Hình ảnh: ')
                        ->setAttributes([
                            "id"            => $key . "_hidden",
                            'class'         => 'form-control',
                        ]);
                    $this->add($data);

                    // $data = new Element\File($key);
                    // $data->setLabel($item['name'])
                    // ->setAttributes([
                    //     'id'    => $key
                    // ]);
                    // $this->add($data);
                }else{
                    $data = new Element\Text($key);
                    $data->setLabel($item['name'])
                        ->setAttributes([
                            'class'         => 'form-control',
                            'placeholder'   => $item['name']
                        ]);
                    $this->add($data);
                }
            }
        }
        
        if($this->userId == '1'){
            $prefixCode = new Element\Text('prefix_code');
            $prefixCode->setLabel('Tiếp đầu ngữ mã PIN: ')
                ->setAttributes([
                    'class' => 'form-control',
                    'placeholder'   => 'Tiếp đầu ngữ mã PIN: '
                ]);
            $this->add($prefixCode);
            
            $prefixSerial = new Element\Text('prefix_serial');
            $prefixSerial->setLabel('Tiếp đầu ngữ serial: ')
                ->setAttributes([
                    'class' => 'form-control',
                    'placeholder'   => 'Tiếp đầu ngữ serial: '
                ]);
            $this->add($prefixSerial);
            
            $serialBegin = new Element\Text('serial_begin');
            $serialBegin->setLabel('Số serial bắt đầu: ')
                ->setAttributes([
                    'class' => 'form-control',
                    'value' => '1',
                    'placeholder'   => 'Số serial bắt đầu: '
                ]);
            $this->add($serialBegin);
            
            $messageSuccess = new Element\Textarea('message_success');
            $messageSuccess->setLabel('Tin nhắn đúng: ')
                ->setAttributes([
                    'rows'       => 2,
                    'id' => 'message_success',
                    'class' => 'form-control',
                    'placeholder'   => 'Tin nhắn đúng: '
                ]);
            $this->add($messageSuccess);
            
            $messageChecked = new Element\Textarea('message_checked');
            $messageChecked->setLabel('Tin nhắn đã kiểm tra: ')
                ->setAttributes([
                    'rows'       => 2,
                    'id' => 'message_checked',
                    'class' => 'form-control',
                    'placeholder'   => 'Tin nhắn đã kiểm tra: '
                ]);
            $this->add($messageChecked);
            
            $messageOutdate = new Element\Textarea('message_outdate');
            $messageOutdate->setLabel('Tin nhắn hết hạn: ')
                ->setAttributes([
                    'rows'       => 2,
                    'id' => 'message_outdate',
                    'class' => 'form-control',
                    'placeholder'   => 'Tin nhắn hết hạn: '
                ]);
            $this->add($messageOutdate);

            $messageInvalid = new Element\Textarea('message_invalid');
            $messageInvalid->setLabel('Tin nhắn sai mã: ')
                ->setAttributes([
                    'rows'       => 2,
                    'id' => 'message_invalid',
                    'class' => 'form-control',
                    'placeholder'   => 'Tin nhắn sai mã: '
                ]);
            $this->add($messageInvalid);
        }
        
        $status = new Element\Radio('status');
        $status->setLabel('Trạng thái: ')
            ->setAttributes([
                'value'     => '1',
                'class'     => 'radio-col-brown',
                'style'     => 'margin-left: 10px; margin-right: 3px;'
            ])
            ->setValueOptions(
                [
                'female' => [
                    'label' => 'Hoạt động', 
                    'label_attributes' => ['for' => 'active'], 
                    'value' => '1', 
                    'attributes' => ['id' => 'active']
                ],
                'male' => [
                    'label' => 'Không hoạt động', 
                    'label_attributes' => ['for' => 'inactive'], 
                    'value' => '0', 
                    'attributes' => ['id' => 'inactive']
                ]
            ]);
        $this->add($status);
        
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
                            NotEmpty::IS_EMPTY => "Tên sản phẩm không được để trống!"
                        ]
                    ]
                ],
                [
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                        'min' => 2,
                        'max' => 256,
                        'messages' => [
                            StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự",
                            StringLength::TOO_SHORT => "Vui lòng nhập trên %min% ký tự"
                        ]
                    ]
                ],
            ]
        ]);

        if($this->userId == '1'){
            $inputFilter->add([
                'name' => 'prefix_code',
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
                    ],
                ]
            ]);
            
            $inputFilter->add([
                'name' => 'prefix_serial',
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
                    ],
                ]
            ]);
    
            $inputFilter->add([
                'name' => 'message_success',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags']
                ],
                'validators' => [[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                            'max' => 160,
                            'messages' => [
                                StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
                            ]
                        ]
                    ],
                ]
            ]);
    
            $inputFilter->add([
                'name' => 'message_checked',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags']
                ],
                'validators' => [[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                            'max' => 160,
                            'messages' => [
                                StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
                            ]
                        ]
                    ],
                ]
            ]);
    
            $inputFilter->add([
                'name' => 'message_outdate',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags']
                ],
                'validators' => [[
                    'name' => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => [
                            'max' => 160,
                            'messages' => [
                                StringLength::TOO_LONG => "Vui lòng nhập dưới %max% ký tự"
                            ]
                        ]
                    ],
                ]
            ]);
        }
        
    }
}