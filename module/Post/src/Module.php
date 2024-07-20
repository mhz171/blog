<?php

namespace Post;

use Post\Factory\PostControllerFactory;
use Post\Factory\PostTableFactory;
use Post\Factory\PostTableGatewayFactory;
use Post\Model\PostTable;
use Post\Model\Post;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Post\Controller\PostController;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
// use Model\PostTableGateway;

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
                Post\Service\PostService::class => \Laminas\ServiceManager\Factory\InvokableFactory::class,
                Post\Service\TimeService::class => \Laminas\ServiceManager\Factory\InvokableFactory::class,
            ],
        ];
    }
}
