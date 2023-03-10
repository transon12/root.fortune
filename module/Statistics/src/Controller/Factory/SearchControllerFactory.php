<?php

namespace Statistics\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use Admin\Model\PxtAuthentication;
use Codes\Model\Codes;
use Settings\Model\Settings;
use Storehouses\Model\Products;
use Settings\Model\Messages;
use Statistics\Controller\SearchController;
use Storehouses\Model\Agents;
use Storehouses\Model\Storehouses;

class SearchControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new SearchController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Codes::class),
            $container->get(Products::class),
            $container->get(Messages::class),
            $container->get(Agents::class),
            $container->get(Storehouses::class)
        );
    }
}