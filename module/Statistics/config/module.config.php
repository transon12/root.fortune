<?php

namespace Statistics;

use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'statistics' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/statistics',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'index_slash' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '[/]',
                            'defaults'  => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'index' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/index[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'promotions' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/promotions[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\PromotionsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'dials' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/dials[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\DialsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'search' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/search[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\SearchController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'warranties' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/warranties[/][:action[/][:id][/][:warranty_id]]',
                            'defaults'  => [
                                'controller' => Controller\WarrantiesController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
       'factories' => [
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\PromotionsController::class => Controller\Factory\PromotionsControllerFactory::class,
            Controller\DialsController::class => Controller\Factory\DialsControllerFactory::class,
            Controller\SearchController::class => Controller\Factory\SearchControllerFactory::class,
            Controller\WarrantiesController::class => Controller\Factory\WarrantiesControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
