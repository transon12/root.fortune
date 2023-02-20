<?php
namespace Settings\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Settings\Controller\CitiesController;
use Settings\Model\Cities;
use Settings\Model\Countries;

class CitiesControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new CitiesController(
            $container->get(PxtAuthentication::class),
            $container->get(Cities::class),
            $container->get(Countries::class)
        );
    }
}