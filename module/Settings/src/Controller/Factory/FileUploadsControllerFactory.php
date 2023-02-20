<?php
namespace Settings\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Controller\FileUploadsController;
use Settings\Model\FileUploads;
use Settings\Model\Settings;

class FileUploadsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new FileUploadsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(FileUploads::class)
        );
    }
}