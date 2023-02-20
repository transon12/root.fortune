<?php

namespace Persons\Controller;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Persons\Form\Index\DeleteForm;
use Persons\Form\Index\UploadFileForm;
use Persons\Model\Departments;
use Settings\Model\FileUploads;
use Settings\Model\Settings;
use Zend\Filter\File\RenameUpload;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class DocumentsController extends AdminCore{
    
    public $entitySettings;
    public $entityFileUploads;

    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings,
    FileUploads $entityFileUploads){
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityFileUploads = $entityFileUploads;
    }

    public function indexAction(){
        $this->layout()->setTemplate('empty/layout');        
        $id = $this->params()->fromRoute('id', 0);
        $arrFileUploads = $this->entityFileUploads->fetchGroupIdNotNull(['id'=>$id]);
        return new ViewModel([
            'arrFileUploads' => $arrFileUploads,
        ]);
    }
}
