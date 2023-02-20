<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '[/][:qrcode]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            /*'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/application[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],*/
            'api' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api[/][:action[/]]',
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'ck' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/ck[/][:id]',
                    'defaults' => [
                        'controller' => Controller\CkController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\ApiController::class => Controller\Factory\ApiControllerFactory::class,
            Controller\CkController::class => Controller\Factory\CkControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'check/layout'            => __DIR__ . '/../view/layout/layout-check.phtml',
            'info/layout'             => __DIR__ . '/../view/layout/layout-info.phtml',
            'layout_01'               => __DIR__ . '/../view/layout/layout-01.phtml',
            'layout_02'               => __DIR__ . '/../view/layout/layout-02.phtml',
            'layout_03'               => __DIR__ . '/../view/layout/layout-03.phtml',
            'layout_04'               => __DIR__ . '/../view/layout/layout-04.phtml',
            'layout_05'               => __DIR__ . '/../view/layout/layout-05.phtml',
            'layout_06'               => __DIR__ . '/../view/layout/layout-06.phtml',
            'layout_07'               => __DIR__ . '/../view/layout/layout-07.phtml',
            'layout_09'               => __DIR__ . '/../view/layout/layout-09.phtml',
            'layout_10'               => __DIR__ . '/../view/layout/layout-10.phtml',
            'layout_01_info'          => __DIR__ . '/../view/layout/layout-01-info.phtml',
            'layout_02_info'          => __DIR__ . '/../view/layout/layout-02-info.phtml',
            'layout_03_info'          => __DIR__ . '/../view/layout/layout-03-info.phtml',
            'layout_04_info'          => __DIR__ . '/../view/layout/layout-04-info.phtml',
            'layout_05_info'          => __DIR__ . '/../view/layout/layout-05-info.phtml',
            'layout_06_info'          => __DIR__ . '/../view/layout/layout-06-info.phtml',
            'layout_07_info'          => __DIR__ . '/../view/layout/layout-07-info.phtml',
            'layout_09_info'          => __DIR__ . '/../view/layout/layout-09-info.phtml',
            'layout_10_info'          => __DIR__ . '/../view/layout/layout-10-info.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
