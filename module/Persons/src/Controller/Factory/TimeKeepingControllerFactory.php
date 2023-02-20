<?php

namespace Persons\Controller\Factory;

use Admin\Model\Positions;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Interop\Container\ContainerInterface;
use Persons\Controller\TimeKeepingController;
use Persons\Model\LeaveLists;
use Persons\Model\Profiles;
use Persons\Model\TimeKeeping;
use Settings\Model\Settings;
use Zend\ServiceManager\Factory\FactoryInterface;

class TimeKeepingControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null){
        return new TimeKeepingController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Users::class),
            $container->get(Profiles::class),
            $container->get(TimeKeeping::class)
        );
    }
}