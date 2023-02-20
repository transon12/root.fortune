<?php

namespace Persons\Controller\Factory;

use Admin\Model\Groups;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Interop\Container\ContainerInterface;
use Persons\Controller\RewardDisciplinesController;
use Persons\Model\Profiles;
use Persons\Model\RewardDisciplines;
use Settings\Model\Settings;
use Zend\ServiceManager\Factory\FactoryInterface;

class RewardDisciplinesControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null){
        return new RewardDisciplinesController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Users::class),
            $container->get(Profiles::class),
            $container->get(RewardDisciplines::class),
            $container->get(Groups::class)
        );
    }
}