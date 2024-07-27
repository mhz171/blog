<?php

namespace Post;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Post\Controller\PostController;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
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
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'post' => __DIR__ . '/../view',
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
];
