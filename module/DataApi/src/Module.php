<?php
namespace DataApi;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    public function getServiceConfig(){
        return [
            'factories' => [
                Model\News::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\News($adapter);
                },
                Model\Companies::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Companies($adapter);
                },
                Model\CompanyConfigs::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\CompanyConfigs($adapter);
                },
                Model\Storehouses::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Storehouses($adapter);
                },
                Model\Messages::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Messages($adapter);
                },
                Model\Agents::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Agents($adapter);
                },
                Model\Products::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Products($adapter);
                },
                Model\Codes::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Codes($adapter);
                },
                Model\Orders::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Orders($adapter);
                },
                Model\Missions::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Missions($adapter);
                },
                Model\OrderDetails::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\OrderDetails($adapter);
                },
                Model\MissionDetails::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\MissionDetails($adapter);
                },
                Model\Technologies::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Technologies($adapter);
                },
                Model\Users::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Users($adapter);
                },
            ],
        ];
    }
}
