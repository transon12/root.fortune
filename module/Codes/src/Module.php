<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Codes;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    public function getServiceConfig(){
        return [
            'factories' => [
                Model\Blocks::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Blocks($adapter);
                },
                Model\Codes::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Codes($adapter);
                },
                Model\CodeRoots::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\CodeRoots($adapter);
                },
                Model\CodeFiles::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\CodeFiles($adapter);
                },
                Model\CodeRootsQrcode::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\CodeRootsQrcode($adapter);
                },
                Model\CodeFilesQrcode::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\CodeFilesQrcode($adapter);
                },
            ],
        ];
    }
}
