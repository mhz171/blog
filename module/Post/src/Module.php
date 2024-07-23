<?php

namespace Post;

use Doctrine\ORM\EntityManagerInterface;
use Post\Factory\PostControllerFactory;
use Post\Factory\PostServiceFactory;
use Post\Factory\PostTableFactory;
use Post\Factory\PostTableGatewayFactory;
use Post\Model\PostTable;
use Post\Model\Post;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Post\Controller\PostController;
use Post\Service\PostService;
use Post\Service\TimeService;
use Psr\Container\ContainerInterface;


class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                PostController::class => PostControllerFactory::class,
            ]
        ];
    }


    public function getServiceConfig()
    {
        return [
            'factories' => [
                PostTable::class => PostTableFactory::class,
                Model\PostTableGateway::class => PostTableGatewayFactory::class,
                PostService::class => PostServiceFactory::class,
                TimeService::class => \Laminas\ServiceManager\Factory\InvokableFactory::class,
            ],
        ];
    }
}
