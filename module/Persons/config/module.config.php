<?php

namespace Persons;

use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'persons' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/persons',
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
                    'profiles' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/profiles[/][:action[/][:id[/][:datas_key]]]',
                            'defaults'  => [
                                'controller' => Controller\ProfilesController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'documents' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/documents[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\DocumentsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'recruitments' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/recruitments[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\RecruitmentsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'trainings' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/trainings[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\TrainingsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'reward-disciplines' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/reward-disciplines[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\RewardDisciplinesController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'leaves' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/leaves[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\LeavesController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'leave-applies' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/leave-applies[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\LeaveAppliesController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'leave-requests' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/leave-requests[/][:action[/][:user_id[/][:id]]]',
                            'defaults'  => [
                                'controller' => Controller\LeaveRequestsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'evaluations' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/evaluations[/][:action[/][:id[/][:evaluation_id]]]',
                            'defaults'  => [
                                'controller' => Controller\EvaluationsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'kpis' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/kpis[/][:action[/][:user_id[/][:id]]]',
                            'defaults'  => [
                                'controller' => Controller\KpisController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'notifications' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/notifications[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\NotificationsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'time-keeping' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/time-keeping[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\TimeKeepingController::class,
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
            Controller\ProfilesController::class => Controller\Factory\ProfilesControllerFactory::class,
            Controller\DocumentsController::class => Controller\Factory\DocumentsControllerFactory::class,
            Controller\RecruitmentsController::class => Controller\Factory\RecruitmentsControllerFactory::class,
            Controller\TrainingsController::class => Controller\Factory\TrainingsControllerFactory::class,
            Controller\RewardDisciplinesController::class => Controller\Factory\RewardDisciplinesControllerFactory::class,
            Controller\LeavesController::class => Controller\Factory\LeavesControllerFactory::class,
            Controller\LeaveAppliesController::class => Controller\Factory\LeaveAppliesControllerFactory::class,
            Controller\LeaveRequestsController::class => Controller\Factory\LeaveRequestsControllerFactory::class,
            Controller\EvaluationsController::class => Controller\Factory\EvaluationsControllerFactory::class,
            Controller\KpisController::class => Controller\Factory\KpisControllerFactory::class,
            Controller\NotificationsController::class => Controller\Factory\NotificationsControllerFactory::class,
            Controller\TimeKeepingController::class => Controller\Factory\TimeKeepingControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
