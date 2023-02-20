<?php
namespace Promotions\Form\Dials;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\File\Size;
use Zend\Validator\File\MimeType;

class AddForm extends Form{
    
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
            
        $winMore = new Element\Radio('win_more');
        $winMore->setLabel('Trúng nhiều lần: ')
            ->setAttributes([
                'value'     => '0',
                'class'     => 'radio-col-brown',
                'style'     => 'margin-left: 10px; margin-right: 3px;'
            ])
            ->setValueOptions(['win_more_0' => [
                    'label' => 'Không',
                    'label_attributes' => ['for' => 'win_more_0'],
                    'value' => '0',
                    'attributes' => ['id' => 'win_more_0']
                ],
                'win_more_1' => [
                    'label' => 'Có',
                    'label_attributes' => ['for' => 'win_more_1'],
                    'value' => '1',
                    'attributes' => ['id' => 'win_more_1']
                ]
            ]);
        $this->add($winMore);
        
        $background = new Element\File('background');
        $background->setLabel('Hình nền: ')
            ->setAttributes([
                'id' => 'background'
            ]);
        $this->add($background);
            
        $musicBackground = new Element\File('music_background');
        $musicBackground->setLabel('Nhạc nền: ')
            ->setAttributes([
                'id' => 'music_background'
            ]);
        $this->add($musicBackground);
            
        $musicDial = new Element\File('music_dial');
        $musicDial->setLabel('Nhạc quay số: ')
            ->setAttributes([
                'id' => 'music_dial'
        ]);
        $this->add($musicDial);
            
        $musicWin = new Element\File('music_win');
        $musicWin->setLabel('Nhạc trúng thưởng: ')
            ->setAttributes([
                'id' => 'music_win'
        ]);
        $this->add($musicWin);
        
        
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
            'type'     => 'Zend\InputFilter\FileInput',
            'name'     => 'background',
            'required' => false,
            'validators' => [[
                'name'    => 'Zend\Validator\File\MimeType',
                'options' => [
                    'mimeType'  => [
                        'image/jpeg', 'image/png', 'image/jpg', 'image/gif'
                    ],
                    'messages' => [
                        MimeType::FALSE_TYPE => "Định dạng %type% không cho phép!",
                        MimeType::NOT_DETECTED => "Mimetype không xác định",
                        MimeType::NOT_READABLE => "Mimetype không thể đọc"
                    ]
                ]
            ],[
                'name'    => 'FileSize',
                'options' => [
                    'max'  => 1024*10000,
                    'messages' => [
                        Size::TOO_BIG => "File quá lớn, chỉ cho phép file dưới %max%"
                    ]
                ]
            ],
            ]
        ]);
        
        $inputFilter->add([
            'type'     => 'Zend\InputFilter\FileInput',
            'name'     => 'music_background',
            'required' => false,
            'validators' => [[
                'name'    => 'Zend\Validator\File\MimeType',
                'options' => [
                    'mimeType'  => [
                        'audio/mpeg3', 'audio/x-mpeg-3', 'video/mpeg', 'video/x-mpeg', 'audio/mpeg', 'application/octet-stream'
                    ],
                    'messages' => [
                        MimeType::FALSE_TYPE => "Định dạng %type% không cho phép!",
                        MimeType::NOT_DETECTED => "Mimetype không xác định",
                        MimeType::NOT_READABLE => "Mimetype không thể đọc"
                    ]
                ]
            ],[
                'name'    => 'FileSize',
                'options' => [
                    'max'  => 1024*10000,
                    'messages' => [
                        Size::TOO_BIG => "File quá lớn, chỉ cho phép file dưới %max%"
                    ]
                ]
            ],
            ]
        ]);
        
        $inputFilter->add([
            'type'     => 'Zend\InputFilter\FileInput',
            'name'     => 'music_dial',
            'required' => false,
            'validators' => [[
                'name'    => 'Zend\Validator\File\MimeType',
                'options' => [
                    'mimeType'  => [
                        'audio/mpeg3', 'audio/x-mpeg-3', 'video/mpeg', 'video/x-mpeg', 'audio/mpeg', 'application/octet-stream'
                    ],
                    'messages' => [
                        MimeType::FALSE_TYPE => "Định dạng %type% không cho phép!",
                        MimeType::NOT_DETECTED => "Mimetype không xác định",
                        MimeType::NOT_READABLE => "Mimetype không thể đọc"
                    ]
                ]
            ],[
                'name'    => 'FileSize',
                'options' => [
                    'max'  => 1024*10000,
                    'messages' => [
                        Size::TOO_BIG => "File quá lớn, chỉ cho phép file dưới %max%"
                    ]
                ]
            ],
            ]
        ]);
        
        $inputFilter->add([
            'type'     => 'Zend\InputFilter\FileInput',
            'name'     => 'music_win',
            'required' => false,
            'validators' => [[
                'name'    => 'Zend\Validator\File\MimeType',
                'options' => [
                    'mimeType'  => [
                        'audio/mpeg3', 'audio/x-mpeg-3', 'video/mpeg', 'video/x-mpeg', 'audio/mpeg', 'application/octet-stream'
                    ],
                    'messages' => [
                        MimeType::FALSE_TYPE => "Định dạng %type% không cho phép!",
                        MimeType::NOT_DETECTED => "Mimetype không xác định",
                        MimeType::NOT_READABLE => "Mimetype không thể đọc"
                    ]
                ]
            ],[
                'name'    => 'FileSize',
                'options' => [
                    'max'  => 1024*10000,
                    'messages' => [
                        Size::TOO_BIG => "File quá lớn, chỉ cho phép file dưới %max%"
                    ]
                ]
            ],
            ]
        ]);
    }
}