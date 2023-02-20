<?php

namespace Settings;

use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'settings' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/settings',
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
                    'phones' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/phones[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\PhonesController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'cities' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/cities[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\CitiesController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'districts' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/districts[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\DistrictsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'wards' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/wards[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\WardsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'companies' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/companies[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\CompaniesController ::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'company-configs' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/company-configs[/][:action[/][:id[/][:content_key]]]',
                            'defaults'  => [
                                'controller' => Controller\CompanyConfigsController ::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'file-uploads' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/file-uploads[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\FileUploadsController::class,
                                'action'     => 'index',
                            ],
                        ]
                    ],
                    'technologies' => [
                        'type'  => Segment::class,
                        'options'   => [
                            'route' => '/technologies[/][:action[/][:id]]',
                            'defaults'  => [
                                'controller' => Controller\TechnologiesController ::class,
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
            Controller\PhonesController::class => Controller\Factory\PhonesControllerFactory::class,
            Controller\CitiesController::class => Controller\Factory\CitiesControllerFactory::class,
            Controller\DistrictsController::class => Controller\Factory\DistrictsControllerFactory::class,
            Controller\WardsController::class => Controller\Factory\WardsControllerFactory::class,
            Controller\CompaniesController::class => Controller\Factory\CompaniesControllerFactory::class,
            Controller\CompanyConfigsController::class => Controller\Factory\CompanyConfigsControllerFactory::class,
            Controller\FileUploadsController::class => Controller\Factory\FileUploadsControllerFactory::class,
            Controller\TechnologiesController::class => Controller\Factory\TechnologiesControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
