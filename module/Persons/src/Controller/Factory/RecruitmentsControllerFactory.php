<?php

namespace Persons\Controller\Factory;

use Admin\Model\Groups;
use Admin\Model\PxtAuthentication;
use Interop\Container\ContainerInterface;
use Persons\Controller\RecruitmentsController;
use Persons\Model\Recruitments;
use Settings\Model\Settings;
use Zend\ServiceManager\Factory\FactoryInterface;

class RecruitmentsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null){
        return new RecruitmentsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Recruitments::class),
            $container->get(Groups::class)
        );
    }
}