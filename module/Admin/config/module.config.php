<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'admin' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/admin',
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
                    'users' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/users[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\UsersController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'groups' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/groups[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\GroupsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'positions' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/positions[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\PositionsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'user-addresses' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/user-addresses[/][:action[/][:id][/][:user_addresses_id]]',
                            'defaults'  => [
                                'controller' => Controller\UserAddressesController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'user-phones' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/user-phones[/][:action[/][:id][/][:user_phones_id]]',
                            'defaults'  => [
                                'controller' => Controller\UserPhonesController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'user-identity-cards' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/user-identity-cards[/][:action[/][:id][/][:user_identity_cards_id]]',
                            'defaults'  => [
                                'controller' => Controller\UserIdentityCardsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'labour-contracts' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/labour-contracts[/][:action[/][:id][/][:labour-contracts_id]]',
                            'defaults'  => [
                                'controller' => Controller\LabourContractsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'user-crms' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/user-crms[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\UserCrmsController::class,
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
            Controller\UsersController::class => Controller\Factory\UsersControllerFactory::class,
            Controller\GroupsController::class => Controller\Factory\GroupsControllerFactory::class,
            Controller\PositionsController::class => Controller\Factory\PositionsControllerFactory::class,
            Controller\UserAddressesController::class => Controller\Factory\UserAddressesControllerFactory::class,
            Controller\UserPhonesController::class => Controller\Factory\UserPhonesControllerFactory::class,
            Controller\UserIdentityCardsController::class => Controller\Factory\UserIdentityCardsControllerFactory::class,
            Controller\LabourContractsController::class => Controller\Factory\LabourContractsControllerFactory::class,
            Controller\UserCrmsController::class => Controller\Factory\UserCrmsControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'admin/layout' => __DIR__ . '/../view/layout/layout-admin.phtml',
            'empty/layout' => __DIR__ . '/../view/layout/layout-empty.phtml',
            'login/layout' => __DIR__ . '/../view/layout/layout-login.phtml',
            'iframe/layout' => __DIR__ . '/../view/layout/layout-iframe.phtml',
            'not-permission/layout' => __DIR__ . '/../view/layout/layout-not-permission.phtml',
            'ajax-not-permission/layout' => __DIR__ . '/../view/layout/layout-ajax-not-permission.phtml',
            
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
