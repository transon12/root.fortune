<?php

namespace Promotions;

use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'promotions' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/promotions',
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
                    'dials' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/dials[/][:action[/][:id[/][:winner_dial_id]]]',
                            'defaults'  => [
                                'controller' => Controller\DialsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'list-dials' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/list-dials[/][:action[/][:id[/][:list_dials_id]]]',
                            'defaults'  => [
                                'controller' => Controller\ListDialsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'prizes' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/prizes[/][:action[/][:id][/][:prizes_id]]',
                            'defaults'  => [
                                'controller' => Controller\PrizesController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'order' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/order[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\OrderController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'offers' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/offers[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\OffersController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'plusScore' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/plusScore[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'plusScore',
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
            Controller\DialsController::class => Controller\Factory\DialsControllerFactory::class,
            Controller\ListDialsController::class => Controller\Factory\ListDialsControllerFactory::class,
            Controller\PrizesController::class => Controller\Factory\PrizesControllerFactory::class,
            Controller\OrderController::class => Controller\Factory\OrderControllerFactory::class,
            Controller\OffersController::class => Controller\Factory\OffersControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'dial/layout' => __DIR__ . '/../view/layout/layout-dial.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
