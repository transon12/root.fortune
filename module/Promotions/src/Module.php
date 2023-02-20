<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Promotions;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    public function getServiceConfig(){
        return [
            'factories' => [
                Model\Promotions::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Promotions($adapter);
                },
                Model\PromotionsProducts::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\PromotionsProducts($adapter);
                },
                Model\ListDials::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\ListDials($adapter);
                },
                Model\ListPromotions::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\ListPromotions($adapter);
                },
                Model\WinnerPromotions::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\WinnerPromotions($adapter);
                },
                Model\WinnerDials::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\WinnerDials($adapter);
                },
                Model\Dials::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Dials($adapter);
                },
                Model\DialsPromotions::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\DialsPromotions($adapter);
                },
                Model\Prizes::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Prizes($adapter);
                },
                Model\LogWinners::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\LogWinners($adapter);
                },
                Model\Offers::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Offers($adapter);
                },
                Model\PlusScore::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\PlusScore($adapter);
                },
            ],
        ];
    }
}
