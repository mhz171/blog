<?php

namespace Post\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Post\Repository\PostRepository;
use Post\Service\PostService;


class PostServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $postRepository = $container->get(PostRepository::class);
        return new PostService($postRepository);
    }
}