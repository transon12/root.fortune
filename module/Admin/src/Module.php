<?php

namespace Admin;

class Module
{
    
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    public function getServiceConfig(){
        return [
            'factories' => [
                Model\Users::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Users($adapter);
                },
                Model\Groups::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Groups($adapter);
                },
                Model\PxtAuthentication::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\PxtAuthentication($adapter);
                },
                Model\Positions::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Positions($adapter);
                },
                Model\GroupsPositionsUsers::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\GroupsPositionsUsers($adapter);
                },
                Model\Mcas::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Mcas($adapter);
                },
                Model\McasGroups::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\McasGroups($adapter);
                },
                Model\McasUsers::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\McasUsers($adapter);
                },
                Model\UserAddresses::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\UserAddresses($adapter);
                },
                Model\UserPhones::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\UserPhones($adapter);
                },
                Model\UserIdentityCards::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\UserIdentityCards($adapter);
                },
                Model\McasUsersAllow::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\McasUsersAllow($adapter);
                },
                Model\McasUsersDeny::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\McasUsersDeny($adapter);
                },
                Model\LabourContracts::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\LabourContracts($adapter);
                },
                Model\UserCrms::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\UserCrms($adapter);
                },
            ],
        ];
    }
    
}
