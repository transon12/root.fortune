<?php

namespace DataApi\Controller\Factory;

use Admin\Model\PxtAuthentication;
use DataApi\Model\Codes;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use DataApi\Controller\StorehousesController;
use DataApi\Model\Agents;
use DataApi\Model\Products;
use DataApi\Model\Storehouses;

class StorehousesControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new StorehousesController(
            $container->get(PxtAuthentication::class),
            $container->get(Storehouses::class),
            $container->get(Codes::class),
            $container->get(Products::class),
            $container->get(Agents::class)
        );
    }
}