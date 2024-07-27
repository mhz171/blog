<?php

namespace Post;

use Laminas\Authentication\AuthenticationService;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Post\Controller\Plugin\AuthPlugin;
use Post\Controller\PluginManager;
use Post\Controller\PostController;
use Post\Factory\AuthPluginFactory;
use Post\Factory\PostControllerFactory;
use Post\Factory\PostServiceFactory;
use Post\Factory\PostTableFactory;
use Post\Factory\PostTableGatewayFactory;
use Post\Model\PostTable;
use Post\Service\PostService;
use Post\Service\TimeService;


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
                AuthenticationService::class => InvokableFactory::class,
            ],
        ];
    }
}
