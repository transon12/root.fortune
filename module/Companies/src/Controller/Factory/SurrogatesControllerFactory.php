<?php

namespace Companies\Controller\Factory;

use Admin\Model\Positions;
use Admin\Model\PxtAuthentication;
use Companies\Controller\SurrogatesController;
use Companies\Model\Surrogates;
use Interop\Container\ContainerInterface;
use Settings\Model\Settings;
use Zend\ServiceManager\Factory\FactoryInterface;

class SurrogatesControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null){
        return new SurrogatesController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Surrogates::class),
            $container->get(Positions::class)
        );
        
    }
}