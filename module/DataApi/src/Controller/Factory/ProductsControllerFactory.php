<?php

namespace DataApi\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use DataApi\Controller\ProductsController;
use DataApi\Model\Products;

class ProductsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new ProductsController(
            $container->get(PxtAuthentication::class),
            $container->get(Products::class)
        );
    }
}