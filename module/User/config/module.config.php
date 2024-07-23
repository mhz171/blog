<?php
namespace User;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Post\Controller\PostController;

return [


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
