<?php

namespace Post\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Post\Controller\PostController;
use Post\Model\PostTable;
use Post\Service\PostService;

class PostControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface|\Psr\Container\ContainerInterface $container, $requestedName, array $options = null)
    {
        $table = $container->get(PostTable::class);
        $postService = $container->get(PostService::class);
        return new PostController($table, $postService);
    }
}
