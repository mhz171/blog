<?php
namespace User;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Laminas\Authentication\AuthenticationService;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use User\Controller\AuthController;
use User\Factory\AuthAdapterFactory;
use User\Factory\AuthControllerFactory;
use User\Factory\AuthenticationServiceFactory;
use User\Factory\DoctrineAdapterFactory;
use User\Service\AuthAdapter;
use User\Service\DoctrineAdapter;


return [
    'controllers' => [
        'factories' => [
            authcontroller::class => AuthControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => AuthController::class,
                        'action'     => 'login',
                    ],
                ],
            ],
            'login' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/login',
                    'defaults' => [
                        'controller' => AuthController::class,
                        'action'     => 'login',
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'service_manager' => [
        'factories' => [
//            AuthAdapter::class => AuthAdapterFactory::class,
            AuthenticationService::class => InvokableFactory::class,
            DoctrineAdapter::class => DoctrineAdapterFactory::class,
        ],
    ],
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'User\Entity' => 'user_entities',
                ],
            ],
            'user_entities' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../../module/User/Entity',
                ],
            ],
        ],
    ],
];
