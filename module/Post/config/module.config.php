<?php

namespace Post;


use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Laminas\Authentication\AuthenticationService;
use Laminas\Db\Sql\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Post\Controller\Plugin\AuthPlugin;
use Post\Controller\Plugin\AuthPluginFactory;
use Post\Controller\PostController;

use Post\view\Helper\MenuBar;
use User\Controller\AuthController;
use User\Service\DoctrineAdapter;

return [
    'router' => [
        'routes' => [

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
            'api-post' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/api/post/add',
                    'defaults' => [
                        'controller' => PostController::class,
                        'action' => 'create',
                    ],
                ],
            ],

        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'post' => __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'Post\Entity' => 'post_entities',
                ],
            ],
            'post_entities' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/Entity',
                ],
            ],
        ],
    ],
    'authentication' => [
        'adapters' => [
            'DoctrineAdapter' => DoctrineAdapter::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            AuthPlugin::class => AuthPluginFactory::class,
        ],

    ],

    'view_helpers' => [
        'factories' => [
            MenuBar::class => function($container) {
                $authService = $container->get(AuthenticationService::class);
                return new MenuBar($authService);
            },
        ],
        'aliases' => [
            'menuBar' => MenuBar::class,
        ],
    ],
];
