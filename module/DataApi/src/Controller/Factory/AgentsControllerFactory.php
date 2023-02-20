<?php

namespace DataApi\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use DataApi\Controller\AgentsController;
use DataApi\Model\Agents;

class AgentsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new AgentsController(
            $container->get(PxtAuthentication::class),
            $container->get(Agents::class)
        );
    }
}