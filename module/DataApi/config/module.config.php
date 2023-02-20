<?php

namespace DataApi;

use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'data-api' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/data-api',
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
                            'route' => '/index[/][:action]',
                            'defaults'  => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'code' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/code[/][:action]',
                            'defaults'  => [
                                'controller' => Controller\CodeController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'user' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/user[/][:action]',
                            'defaults'  => [
                                'controller' => Controller\UserController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'storehouses' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/storehouses[/][:action]',
                            'defaults'  => [
                                'controller' => Controller\StorehousesController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'products' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/products[/][:action]',
                            'defaults'  => [
                                'controller' => Controller\ProductsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'agents' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/agents[/][:action]',
                            'defaults'  => [
                                'controller' => Controller\AgentsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'statistic' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/statistic[/][:action]',
                            'defaults'  => [
                                'controller' => Controller\StatisticController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'news' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/news[/][:action]',
                            'defaults'  => [
                                'controller' => Controller\NewsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'companies' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/companies[/][:action]',
                            'defaults'  => [
                                'controller' => Controller\CompaniesController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'orders' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/orders[/][:action]',
                            'defaults'  => [
                                'controller' => Controller\OrdersController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'technologies' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/technologies[/][:action]',
                            'defaults'  => [
                                'controller' => Controller\TechnologiesController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'missions' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/missions[/][:action]',
                            'defaults'  => [
                                'controller' => Controller\MissionsController::class,
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
            Controller\CodeController::class => Controller\Factory\CodeControllerFactory::class,
            Controller\UserController::class => Controller\Factory\UserControllerFactory::class,
            Controller\StorehousesController::class => Controller\Factory\StorehousesControllerFactory::class,
            Controller\ProductsController::class => Controller\Factory\ProductsControllerFactory::class,
            Controller\AgentsController::class => Controller\Factory\AgentsControllerFactory::class,
            Controller\StatisticController::class => Controller\Factory\StatisticControllerFactory::class,
            Controller\NewsController::class => Controller\Factory\NewsControllerFactory::class,
            Controller\CompaniesController::class => Controller\Factory\CompaniesControllerFactory::class,
            Controller\OrdersController::class => Controller\Factory\OrdersControllerFactory::class,
            Controller\TechnologiesController::class => Controller\Factory\TechnologiesControllerFactory::class,
            Controller\MissionsController::class => Controller\Factory\MissionsControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
