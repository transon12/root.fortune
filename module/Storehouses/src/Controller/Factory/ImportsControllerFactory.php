<?php
namespace Storehouses\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Storehouses\Controller\ImportsController;
use Storehouses\Model\Products;
use Codes\Model\Codes;
use Storehouses\Model\Storehouses;

class ImportsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new ImportsController(
            $container->get(Storehouses::class),
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Products::class),
            $container->get(Codes::class)
        );
    }
}