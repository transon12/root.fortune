<?php
namespace Settings\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Settings\Model\Districts;
use Settings\Controller\WardsController;
use Settings\Model\Wards;

class WardsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new WardsController(
            $container->get(PxtAuthentication::class),
            $container->get(Wards::class),
            $container->get(Districts::class)
        );
    }
}