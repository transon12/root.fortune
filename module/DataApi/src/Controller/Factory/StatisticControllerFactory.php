<?php

namespace DataApi\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use DataApi\Controller\StatisticController;
use Admin\Model\PxtAuthentication;
use DataApi\Model\Messages;
use Storehouses\Model\Products;

class StatisticControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new StatisticController(
            $container->get(PxtAuthentication::class),
            $container->get(Messages::class),
            $container->get(Products::class)
        );
    }
}