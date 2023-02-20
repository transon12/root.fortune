<?php
namespace Settings\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Settings\Model\Cities;
use Settings\Controller\DistrictsController;
use Settings\Model\Districts;

class DistrictsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new DistrictsController(
            $container->get(PxtAuthentication::class),
            $container->get(Districts::class),
            $container->get(Cities::class)
        );
    }
}