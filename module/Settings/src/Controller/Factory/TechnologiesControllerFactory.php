<?php

namespace Settings\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Interop\Container\ContainerInterface;
use Settings\Controller\TechnologiesController;
use Settings\Model\Settings;
use Settings\Model\Technologies;
use Zend\ServiceManager\Factory\FactoryInterface;

class TechnologiesControllerFactory implements FactoryInterface{

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new TechnologiesController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Technologies::class)
        );
    }
}