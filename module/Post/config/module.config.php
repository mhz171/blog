<?php

namespace Post;

use Laminas\Router\Http\Literal;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Router\Http\Segment;
use Post\Controller\PostController;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => PostController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'post' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/post[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => PostController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'post' => __DIR__ . '/../view',
        ],
//        'template_map' => [
//            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
//            'post/index/index'        => __DIR__ . '/../view/post/post/index.phtml',
//            'partial/paginator'       => __DIR__ . '/../view/post/partial/paginator.phtml',
//        ],

    ],
//    'view_helpers' => [
//        'factories' => [
//            Laminas\View\Helper\PaginationControl::class => Laminas\ServiceManager\Factory\InvokableFactory::class,
//        ],
//        'aliases' => [
//            'paginationControl' => Laminas\View\Helper\PaginationControl::class,
//        ],
//    ],
];

