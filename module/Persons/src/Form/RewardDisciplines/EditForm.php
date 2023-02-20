<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Persons\Form\RewardDisciplines;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Form\Element;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Identical;
use Zend\Validator\EmailAddress;
use Zend\Validator\Hostname;

class EditForm extends Form{
    
    public function __construct($route = null) {
        parent::__construct();
        
        $this->setAttributes([
            'class' => 'form-horizontal row',
            'id' => 'event-form-modal',
            'route' => $route,
            'enctype' => 'multipart/form-data',
            'data-target' => '#xlarge',
        ]);
        
        $this->setElements();
        $this->validatorForm();
    }
    
    private function setElements(){
        
        $groupId = new Element\Select('group_id');
        $groupId->setLabel('Phòng ban: ')
            ->setAttributes([
                'id' => 'group_id',
                'class' => 'form-control',
                'placeholder'   => 'Phòng ban: '
            ]);
        $this->add($groupId);
        
        $profileId = new Element\Select('profile_id');
        $profileId->setLabel('Họ tên: ')
            ->setAttributes([
                'id' => 'profile_id',
                'class' => 'form-control',
                'placeholder'   => 'Họ tên: '
            ]);
        $this->add($profileId);

        $content = new Element\Textarea('content');
        $content->setLabel('Nội dung: ')
            ->setAttributes([
                'rows' => 2,
                'id' => 'content',
                'class' => 'form-control',
                'placeholder'   => 'Nội dung: '
            ]);
        $this->add($content);
        
        $type = new Element\Radio('type');
        $type->setLabel('Loại: ')
            ->setAttributes([
                'value'     => '1',
                'class'     => 'radio-col-brown',
                'style'     => 'margin-left: 10px; margin-right: 3px;'
            ])
            ->setValueOptions(
                [
                'reward' => [
                    'label' => 'Khen thưởng', 
                    'label_attributes' => ['for' => 'reward'], 
                    'value' => '1', 
                    'attributes' => ['id' => 'reward']
                ],
                'discipline' => [
                    'label' => 'Kỷ luật', 
                    'label_attributes' => ['for' => 'discipline'], 
                    'value' => '0', 
                    'attributes' => ['id' => 'discipline']
                ],
            ]);
        $this->add($type);

        $level = new Element\Text("level");
        $level->setLabel("Mức khen thưởng hoặc kỷ luật: ")
            ->setAttributes([
                'id' => 'level',
                'class' => 'form-control',
                'placeholder' => 'Mức khen thưởng hoặc kỷ luật: '
            ]);
        $this->add($level);
        
        $proposal = new Element\Textarea('proposal');
        $proposal->setLabel('Đề xuất hoặc đánh giá của trưởng bộ phận: ')
            ->setAttributes([
                'rows' => 2,
                'id' => 'proposal',
                'class' => 'form-control',
                'placeholder'   => 'Đề xuất hoặc đánh giá của trưởng bộ phận: '
            ]);
        $this->add($proposal);

        $date = new Element\Text('date');
        $date->setLabel('Thời gian: ')
            ->setAttributes([
                'id' => 'date',
                'class' => 'form-control datetimepicker-input',
                'placeholder'   => 'Thời gian: ',
                'data-toggle' => 'datetimepicker',
            ]);
        $this->add($date);
        
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

       
    }
}