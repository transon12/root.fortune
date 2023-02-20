<?php

namespace Storehouses;

use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'storehouses' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/storehouses',
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
                    'products' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/products[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\ProductsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'agents' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/agents[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\AgentsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'imports' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/imports[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\ImportsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'exports' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/exports[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\ExportsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'bills' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/bills[/][:action[/][:agentid[/][:id]]]',
                            'defaults'  => [
                                'controller' => Controller\BillsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'details' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/details[/][:action[/][:agentid[/][:id[/][:detailid]]]]',
                            'defaults'  => [
                                'controller' => Controller\DetailsController::class,
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
            Controller\ProductsController::class => Controller\Factory\ProductsControllerFactory::class,
            Controller\ImportsController::class => Controller\Factory\ImportsControllerFactory::class,
            Controller\AgentsController::class => Controller\Factory\AgentsControllerFactory::class,
            Controller\ExportsController::class => Controller\Factory\ExportsControllerFactory::class,
            Controller\BillsController::class => Controller\Factory\BillsControllerFactory::class,
            Controller\DetailsController::class => Controller\Factory\DetailsControllerFactory::class,
           
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
