<?php

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => \Doctrine\DBAL\Driver\PDO\MySQL\Driver::class,
                'params' => [
                    'host'     => 'localhost',
                    'port'     => '3306',
                    'user'     => 'root',
                    'password' => 'root',
                    'dbname'   => 'laminas_db',
                ],
            ],
        ],
        'driver' => [
            'orm_default' => [
                'class' => Doctrine\Persistence\Mapping\Driver\MappingDriverChain::class,
                'drivers' => [
                    'Post\Entity' => 'post_entities',
                ],
            ],
            'post_entities' => [
                'class' => Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [__DIR__ . '/../../module/Post/src/Entity'],
            ],
        ],
    ],
];
