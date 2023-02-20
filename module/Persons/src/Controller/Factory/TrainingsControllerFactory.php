<?php

namespace Persons\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Interop\Container\ContainerInterface;
use Persons\Controller\TrainingsController;
use Persons\Model\Profiles;
use Persons\Model\Trainings;
use Settings\Model\Settings;
use Zend\ServiceManager\Factory\FactoryInterface;

class TrainingsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null){
        return new TrainingsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Users::class),
            $container->get(Profiles::class),
            $container->get(Trainings::class)
        );
    }
}