<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Supplies;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    public function getServiceConfig(){
        return [
            'factories' => [
                Model\Supplies::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Supplies($adapter);
                },
                Model\Suppliers::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Suppliers($adapter);
                },
                Model\SuppliesIns::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\SuppliesIns($adapter);
                },
                Model\Proposals::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Proposals($adapter);
                },
                Model\ProposalDetails::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\ProposalDetails($adapter);
                },
                Model\SuppliesOuts::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\SuppliesOuts($adapter);
                },
            ],
        ];
    }
}
