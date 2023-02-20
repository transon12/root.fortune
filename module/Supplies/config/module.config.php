<?php

namespace Supplies;

use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'supplies' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/supplies',
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
                    'suppliers' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/suppliers[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\SuppliersController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'supplies-ins' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/supplies-ins[/][:action[/][:id][/][:supplies_in_id]]',
                            'defaults'  => [
                                'controller' => Controller\SuppliesInsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'proposals' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/proposals[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\ProposalsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'proposal-details' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/proposal-details[/][:action[/][:id][/][:proposal_detail_id]]',
                            'defaults'  => [
                                'controller' => Controller\ProposalDetailsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'supplies-outs' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/supplies-outs[/][:action[/][:id][/][:supplies_out_id]]',
                            'defaults'  => [
                                'controller' => Controller\SuppliesOutsController::class,
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
            Controller\SuppliersController::class => Controller\Factory\SuppliersControllerFactory::class,
            Controller\SuppliesInsController::class => Controller\Factory\SuppliesInsControllerFactory::class,
            Controller\ProposalsController::class => Controller\Factory\ProposalsControllerFactory::class,
            Controller\ProposalDetailsController::class => Controller\Factory\ProposalDetailsControllerFactory::class,
            Controller\SuppliesOutsController::class => Controller\Factory\SuppliesOutsControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
