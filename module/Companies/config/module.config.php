<?php

namespace Companies;

use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'companies' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/companies',
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
                            'route' => '/index[/][:action[/]]',
                            'defaults'  => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'orders' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/orders[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\OrdersController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'addresses' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/addresses[/][:action[/][:id[/][:addressid]]]',
                            'defaults'  => [
                                'controller' => Controller\AddressesController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'surrogates' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/surrogates[/][:action[/][:id[/][:surrogateid]]]',
                            'defaults'  => [
                                'controller' => Controller\SurrogatesController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'order-details' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/order-details[/][:action[/][:id[/][:detailid]]]',
                            'defaults'  => [
                                'controller' => Controller\OrderDetailsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'mission-details' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/mission-details[/][:action[/][:id[/][:detailid]]]',
                            'defaults'  => [
                                'controller' => Controller\MissionDetailsController::class,
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
            Controller\OrdersController::class => Controller\Factory\OrdersControllerFactory::class,
            Controller\AddressesController::class => Controller\Factory\AddressesControllerFactory::class,
            Controller\SurrogatesController::class => Controller\Factory\SurrogatesControllerFactory::class,
            Controller\OrderDetailsController::class => Controller\Factory\OrderDetailsControllerFactory::class,
            Controller\MissionDetailsController::class => Controller\Factory\MissionDetailsControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
