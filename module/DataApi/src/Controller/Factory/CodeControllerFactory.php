<?php

namespace DataApi\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use DataApi\Controller\CodeController;
use Codes\Model\Codes;
use Settings\Model\StatisticChecks;
use Storehouses\Model\Agents;
use Storehouses\Model\Products;
use Storehouses\Model\Storehouses;

class CodeControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new CodeController(
            $container->get(PxtAuthentication::class),
            $container->get(Codes::class),
            $container->get(Storehouses::class),
            $container->get(Products::class),
            $container->get(Agents::class),
            $container->get(StatisticChecks::class)
        );
    }
}