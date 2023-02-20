<?php

namespace Persons\Controller\Factory;

use Admin\Model\Positions;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Interop\Container\ContainerInterface;
use Persons\Controller\ProfilesController;
use Persons\Model\LeaveLists;
use Persons\Model\Profiles;
use Settings\Model\Settings;
use Zend\ServiceManager\Factory\FactoryInterface;

class ProfilesControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null){
        return new ProfilesController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Users::class),
            $container->get(Profiles::class),
            $container->get(LeaveLists::class),
            $container->get(Positions::class)
        );
    }
}