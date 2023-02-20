<?php

namespace Companies\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Companies\Controller\AddressesController;
use Companies\Model\Addresses;
use Interop\Container\ContainerInterface;
use Settings\Model\Settings;
use Zend\ServiceManager\Factory\FactoryInterface;

class AddressesControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null){
        return new AddressesController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Addresses::class)
        );
    }
}