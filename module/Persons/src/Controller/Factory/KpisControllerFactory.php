<?php

namespace Persons\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Interop\Container\ContainerInterface;
use Persons\Controller\KpisController;
use Persons\Model\Profiles;
use Persons\Model\UserKpis;
use Settings\Model\Settings;
use Zend\ServiceManager\Factory\FactoryInterface;

class KpisControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null){
        return new KpisController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(UserKpis::class),
            $container->get(Users::class),
            $container->get(Profiles::class)
        );
    }
}