<?php

namespace Post;

use Post\Factory\PostControllerFactory;
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
                PostTable::class => function ($container) {
                    $tableGateway = $container->get(Model\PostTableGateway::class);
                    return new PostTable($tableGateway);
                },
                Model\PostTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Post());
                    return new TableGateway('posts', $dbAdapter, null, $resultSetPrototype);
                },
                // PostService::class => function ($container) {
                //     return new PostService($container->get(Post::class));
                // },
                Post\Service\PostService::class => \Laminas\ServiceManager\Factory\InvokableFactory::class,
            ],
        ];
    }
}
