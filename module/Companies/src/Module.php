<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Companies;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    public function getServiceConfig(){
        return [
            'factories' => [
                Model\Orders::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Orders($adapter);
                },
                Model\Addresses::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Addresses($adapter);
                },
                Model\Surrogates::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Surrogates($adapter);
                },
                Model\OrderDetails::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\OrderDetails($adapter);
                },
                Model\MissionDetails::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\MissionDetails($adapter);
                },
                Model\Missions::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Missions($adapter);
                },
            ],
        ];
    }
}
