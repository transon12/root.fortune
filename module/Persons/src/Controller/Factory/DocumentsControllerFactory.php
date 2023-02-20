<?php

namespace Persons\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Interop\Container\ContainerInterface;
use Persons\Controller\DocumentsController;
use Persons\Model\Departments;
use Settings\Model\FileUploads;
use Settings\Model\Settings;
use Zend\ServiceManager\Factory\FactoryInterface;

class DocumentsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null){
        return new DocumentsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(FileUploads::class)
        );
    }
}