<?php

namespace Post\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Post\Controller\PostController;
use Post\Model\PostTable;
use Post\Service\PostService;

class PostControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $postService = $container->get(PostService::class);
        return new PostController($postService);
    }
}
