<?php

namespace Persons\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Interop\Container\ContainerInterface;
use Persons\Controller\NotificationsController;
use Persons\Model\Notifications;
use Persons\Model\Profiles;
use Settings\Model\Settings;
use Zend\ServiceManager\Factory\FactoryInterface;

class NotificationsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null){
        return new NotificationsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Users::class),
            $container->get(Profiles::class),
            $container->get(Notifications::class)
        );
    }
}