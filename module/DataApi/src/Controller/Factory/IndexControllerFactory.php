<?php

namespace DataApi\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use DataApi\Controller\IndexController;
use Admin\Model\PxtAuthentication;

class IndexControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new IndexController(
            $container->get(PxtAuthentication::class)
        );
    }
}