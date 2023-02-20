<?php

namespace DataApi\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use DataApi\Controller\TechnologiesController;
use DataApi\Model\OrderDetails;
use DataApi\Model\Technologies;

class TechnologiesControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new TechnologiesController(
            $container->get(PxtAuthentication::class),
            $container->get(Technologies::class),
            $container->get(OrderDetails::class)
        );
    }
}