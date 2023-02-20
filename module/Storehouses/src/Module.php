<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Storehouses;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    public function getServiceConfig(){
        return [
            'factories' => [
                Model\Products::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Products($adapter);
                },
                Model\Storehouses::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Storehouses($adapter);
                },
                Model\Agents::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Agents($adapter);
                },
                Model\Bills::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Bills($adapter);
                },
                Model\BillDetails::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\BillDetails($adapter);
                },
            ],
        ];
    }
}
