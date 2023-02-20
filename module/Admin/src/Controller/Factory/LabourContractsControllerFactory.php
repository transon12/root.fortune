<?php

namespace Admin\Controller\Factory;

use Admin\Controller\LabourContractsController;
use Admin\Model\LabourContracts;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Interop\Container\ContainerInterface;
use Settings\Model\Settings;
use Zend\ServiceManager\Factory\FactoryInterface;

class LabourContractsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new LabourContractsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(LabourContracts::class),
            $container->get(Users::class)
        );
    }
}