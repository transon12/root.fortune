<?php

namespace Application\Controller\Factory;

use Application\Controller\CkController;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Controller\IndexController;
use Codes\Model\Codes;
use Settings\Model\Companies;
use Settings\Model\Cities;
use Storehouses\Model\Products;
use Storehouses\Model\Agents;
use Settings\Model\Logs;
use Settings\Model\Settings;
use Settings\Model\Wards;

class CkControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new CkController(
            $container->get(Settings::class),
            $container->get(Codes::class),
            $container->get(Companies::class),
            $container->get(Cities::class),
            $container->get(Products::class),
            $container->get(Agents::class),
            $container->get(Logs::class),
            $container->get(Wards::class)
        );
    }
}