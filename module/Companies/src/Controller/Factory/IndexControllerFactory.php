<?php

namespace Companies\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Companies\Controller\IndexController;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class IndexControllerFactory implements FactoryInterface{
    
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new IndexController(
            $container->get(PxtAuthentication::class)
        );
    }
}