<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Settings;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    public function getServiceConfig(){
        return [
            'factories' => [
                Model\Settings::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Settings($adapter);
                },
                Model\Messages::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Messages($adapter);
                },
                Model\Phones::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Phones($adapter);
                },
                Model\LogSms::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\LogSms($adapter);
                },
                Model\Countries::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Countries($adapter);
                },
                Model\Cities::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Cities($adapter);
                },
                Model\Districts::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Districts($adapter);
                },
                Model\Wards::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Wards($adapter);
                },
                Model\Logs::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Logs($adapter);
                },
                Model\Companies::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Companies($adapter);
                },
                Model\CompanyConfigs::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\CompanyConfigs($adapter);
                },
                Model\FileUploads::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\FileUploads($adapter);
                },
                Model\Technologies::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Technologies($adapter);
                },
                Model\StatisticChecks::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\StatisticChecks($adapter);
                },
            ],
        ];
    }
}
