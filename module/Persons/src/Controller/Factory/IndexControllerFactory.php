<?php

namespace Persons\Controller\Factory;

use Admin\Model\Groups;
use Admin\Model\GroupsPositionsUsers;
use Admin\Model\PxtAuthentication;
use Interop\Container\ContainerInterface;
use Persons\Controller\IndexController;
use Persons\Model\Notifications;
use Settings\Model\FileUploads;
use Settings\Model\Settings;
use Zend\ServiceManager\Factory\FactoryInterface;

class IndexControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null){
        return new IndexController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(FileUploads::class),
            $container->get(Groups::class),
            $container->get(GroupsPositionsUsers::class),
            $container->get(Notifications::class)
        );
    }
}