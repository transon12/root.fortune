<?php
namespace Storehouses\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Storehouses\Controller\ExportsController;
use Storehouses\Model\Products;
use Codes\Model\Codes;
use Storehouses\Model\Agents;
use Storehouses\Model\Storehouses;

class ExportsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new ExportsController(
            $container->get(Agents::class),
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Products::class),
            $container->get(Codes::class),
            $container->get(Storehouses::class)
        );
    }
}