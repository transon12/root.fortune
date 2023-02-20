<?php

namespace Statistics;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    public function getServiceConfig(){
        return [
            'factories' => [
                Model\Warranties::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Warranties($adapter);
                },
                Model\TableStatistics::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\TableStatistics($adapter);
                },
            ],
        ];
    }
}
